# Deployment Diagram

```mermaid
flowchart TB
    subgraph ClientLayer["Client Layer"]
        Mobile["Mobile Device<br/>Flutter App"]
        AdminBrowser["Web Browser Admin"]
        GuardBrowser["Web Browser Penjaga"]
    end

    subgraph AppLayer["Application Layer"]
        Laravel["Laravel Backend Server<br/>API + Web Admin + Web Penjaga"]
        Flask["Flask Chatbot Server<br/>RAG + Function Calling"]
    end

    subgraph DataLayer["Data Layer"]
        MySQL[("MySQL Database Server")]
        Storage[("Laravel Storage<br/>images, proofs, GPX-related data")]
        ExportDir[("Chatbot Export Directory<br/>Excel files")]
    end

    subgraph ExternalLayer["External Services"]
        Gemini["Gemini API"]
        Midtrans["Midtrans Payment Gateway"]
        GoogleOAuth["Google OAuth"]
        OpenWeather["OpenWeather API"]
    end

    Mobile -->|HTTPS REST API| Laravel
    Mobile -->|HTTPS POST /api/chat| Flask
    AdminBrowser -->|HTTPS Web Routes| Laravel
    AdminBrowser -->|HTTPS POST /api/chat| Flask
    GuardBrowser -->|HTTPS Web Routes| Laravel
    GuardBrowser -->|HTTPS POST /api/chat| Flask

    Laravel -->|SQL/Eloquent| MySQL
    Laravel -->|file upload/read| Storage
    Flask -->|PyMySQL structured retrieval| MySQL
    Flask -->|write/read Excel| ExportDir

    Flask -->|prompt + tools| Gemini
    Laravel -->|charge/status/notification| Midtrans
    Laravel -->|loginWithGoogle| GoogleOAuth
    Laravel -->|weather current/forecast| OpenWeather
    Midtrans -->|callback /api/midtrans/notification| Laravel
```

## Catatan Deployment

- Flask Chatbot Service tidak ditemukan di dalam folder backend Laravel, tetapi ditemukan pada folder `My_Hiking_Chatbot`.
- Flutter Mobile App tidak ditemukan pada workspace yang dianalisis; node mobile disertakan karena disebut sebagai bagian sistem.
- Chatbot mengambil data langsung dari MySQL untuk RAG, dan juga memanggil Laravel API untuk booking serta percobaan CRUD admin.
