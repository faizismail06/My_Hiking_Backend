<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    /**
     * Get all friends for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->query('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required',
                ], 400);
            }

            // Get accepted friends (both directions)
            $friends = Friend::where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->orWhere('friend_id', $userId);
                })
                ->where('status', 'accepted')
                ->with(['user:id,name,email,phone,profile_picture', 'friend:id,name,email,phone,profile_picture'])
                ->get()
                ->map(function ($friendship) use ($userId) {
                    // Return the other user (not the current user)
                    $friendUser = $friendship->user_id == $userId 
                        ? $friendship->friend 
                        : $friendship->user;
                    
                    return [
                        'id' => $friendUser->id,
                        'name' => $friendUser->name,
                        'email' => $friendUser->email,
                        'phone' => $friendUser->phone,
                        'profile_picture' => $friendUser->profile_picture,
                        'friendship_id' => $friendship->id,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Friends fetched successfully',
                'data' => $friends,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch friends',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending friend requests
     */
    public function pendingRequests(Request $request)
    {
        try {
            $userId = $request->query('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required',
                ], 400);
            }

            // Get pending requests where user is the receiver
            $pendingRequests = Friend::where('friend_id', $userId)
                ->where('status', 'pending')
                ->with(['user:id,name,email,phone,profile_picture'])
                ->get()
                ->map(function ($friendship) {
                    return [
                        'friendship_id' => $friendship->id,
                        'user' => [
                            'id' => $friendship->user->id,
                            'name' => $friendship->user->name,
                            'email' => $friendship->user->email,
                            'phone' => $friendship->user->phone,
                            'profile_picture' => $friendship->user->profile_picture,
                        ],
                        'created_at' => $friendship->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Pending requests fetched successfully',
                'data' => $pendingRequests,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending requests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search users by ID or name
     */
    public function searchUsers(Request $request)
    {
        try {
            $query = $request->query('query');
            $currentUserId = $request->query('user_id');

            if (!$query) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required',
                ], 400);
            }

            $users = User::where(function ($q) use ($query) {
                    $q->where('id', $query)
                      ->orWhere('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->when($currentUserId, function ($q) use ($currentUserId) {
                    $q->where('id', '!=', $currentUserId);
                })
                ->select('id', 'name', 'email', 'phone', 'profile_picture')
                ->limit(20)
                ->get();

            // Check friendship status for each user
            $users = $users->map(function ($user) use ($currentUserId) {
                $friendshipStatus = null;
                
                if ($currentUserId) {
                    $friendship = Friend::where(function ($q) use ($currentUserId, $user) {
                            $q->where('user_id', $currentUserId)
                              ->where('friend_id', $user->id);
                        })
                        ->orWhere(function ($q) use ($currentUserId, $user) {
                            $q->where('user_id', $user->id)
                              ->where('friend_id', $currentUserId);
                        })
                        ->first();
                    
                    if ($friendship) {
                        $friendshipStatus = $friendship->status;
                    }
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_picture' => $user->profile_picture,
                    'friendship_status' => $friendshipStatus,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Users found',
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send friend request
     */
    public function addFriend(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'friend_id' => 'required|exists:users,id|different:user_id',
            ]);

            // Check if friendship already exists
            $existingFriendship = Friend::where(function ($q) use ($request) {
                    $q->where('user_id', $request->user_id)
                      ->where('friend_id', $request->friend_id);
                })
                ->orWhere(function ($q) use ($request) {
                    $q->where('user_id', $request->friend_id)
                      ->where('friend_id', $request->user_id);
                })
                ->first();

            if ($existingFriendship) {
                return response()->json([
                    'success' => false,
                    'message' => 'Friend request already exists',
                    'status' => $existingFriendship->status,
                ], 400);
            }

            $friendship = Friend::create([
                'user_id' => $request->user_id,
                'friend_id' => $request->friend_id,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Friend request sent successfully',
                'data' => $friendship,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send friend request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept friend request
     */
    public function acceptFriend(Request $request, $friendshipId)
    {
        try {
            $friendship = Friend::findOrFail($friendshipId);

            // Only the receiver can accept
            if ($friendship->friend_id != $request->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to accept this request',
                ], 403);
            }

            $friendship->status = 'accepted';
            $friendship->save();

            return response()->json([
                'success' => true,
                'message' => 'Friend request accepted',
                'data' => $friendship,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept friend request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject friend request
     */
    public function rejectFriend(Request $request, $friendshipId)
    {
        try {
            $friendship = Friend::findOrFail($friendshipId);

            // Only the receiver can reject
            if ($friendship->friend_id != $request->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to reject this request',
                ], 403);
            }

            $friendship->status = 'rejected';
            $friendship->save();

            return response()->json([
                'success' => true,
                'message' => 'Friend request rejected',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject friend request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove friend
     */
    public function removeFriend(Request $request, $friendshipId)
    {
        try {
            $friendship = Friend::findOrFail($friendshipId);
            $userId = $request->query('user_id');

            // Both users can remove the friendship
            if ($friendship->user_id != $userId && $friendship->friend_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to remove this friend',
                ], 403);
            }

            $friendship->delete();

            return response()->json([
                'success' => true,
                'message' => 'Friend removed successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove friend',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
