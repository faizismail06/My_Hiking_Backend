<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otpCode;
    public string $userName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otpCode, string $userName = 'Pendaki')
    {
        $this->otpCode = $otpCode;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi OTP Registrasi - MyHiking',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        return "
        <div style='font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 30px; text-align: center;'>
            <div style='max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);'>
                <div style='margin-bottom: 20px;'>
                    <h2 style='color: #2d3748; margin: 0;'>🏔️ MyHiking</h2>
                    <p style='color: #718096; font-size: 14px;'>Aplikasi Verifikasi & Ticketing Pendakian</p>
                </div>
                <hr style='border: none; border-top: 1px solid #edf2f7; margin: 20px 0;'>
                <p style='font-size: 16px; color: #2d3748;'>Halo <strong>{$this->userName}</strong>,</p>
                <p style='font-size: 14px; color: #4a5568;'>Terima kasih telah mendaftar di MyHiking. Gunakan kode OTP di bawah ini untuk menyelesaikan verifikasi registrasi akun Anda:</p>
                
                <div style='background: #e6fffa; border: 2px dashed #319795; border-radius: 12px; padding: 20px; margin: 25px 0;'>
                    <span style='font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #234e52;'>{$this->otpCode}</span>
                </div>
                
                <p style='font-size: 13px; color: #e53e3e;'><strong>⚠️ PENTING:</strong> Kode OTP ini berlaku selama 10 menit. Jangan bagikan kode ini kepada siapapun demi keamanan akun Anda.</p>
                
                <hr style='border: none; border-top: 1px solid #edf2f7; margin: 25px 0;'>
                <p style='font-size: 12px; color: #a0aec0;'>Jika Anda tidak merasa mendaftar di aplikasi MyHiking, silakan abaikan email ini.</p>
            </div>
        </div>
        ";
    }
}
