# Smart Attendance API (Aqlli Davomat Tizimi)

Ushbu loyiha xodimlarning davomatini QR-kodlar orqali boshqarish uchun mo'ljallangan backend (API) tizimidir. Loyiha Laravel 11 framework'ida qurilgan.

## 🚀 Imkoniyatlar

- **JWT Auth**: Foydalanuvchilarni autentifikatsiya qilish (Login).
- **Role-based Access**: Admin va Xodim (Employee) rollari.
- **Admin Panel (API)**: QR-tokenlarni generatsiya qilish (faqat Adminlar uchun).
- **Davomat**: Kelish (check-in) va ketish (check-out) jarayonlarini qayd etish.
- **Vaqt cheklovi**: Faqat belgilangan vaqt oralig'ida davomat qilish imkoniyati.
- **Hisobot**: Oylik davomat hisobotlarini olish.

## 🛠 O'rnatish tartibi

Loyiha ustozingiz yoki boshqa dasturchi tomonidan ishga tushirilishi uchun quyidagi qadamlar bajarilishi kerak:

1. **Loyihani yuklab olish**:
   ```bash
   git clone [repository-link]
   cd smart-attendance
   ```

2. **Kutubxonalarni o'rnatish**:
   ```bash
   composer install
   ```

3. **Muhit (.env) faylini sozlash**:
   `.env.example` faylidan nusxa oling va uni `.env` deb nomlang. Ma'lumotlar bazasi (DB_DATABASE, DB_USERNAME, DB_PASSWORD) sozlamalarini kiriting.

4. **Kalyitlarni generatsiya qilish**:
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

5. **Migratsiya va Seederlarni ishga tushirish**:
   ```bash
   php artisan migrate --seed
   ```

6. **Serverni ishga tushirish**:
   ```bash
   php artisan serve
   ```

## 📋 API Endpoints

- `POST /api/auth/login` - Tizimga kirish (Token olish).
- `POST /api/qr/generate` - QR-token generatsiya qilish (Faqat Admin).
- `POST /api/attendance/check-in` - Davomat qilish (Kelish).
- `POST /api/attendance/check-out` - Davomat qilish (Ketish).
- `GET /api/attendance/month` - Oylik hisobot.

## 🧪 Testlash tartibi (Postman uchun)

API'ni test qilish uchun quyidagi bosqichlarni bajaring:

### 1. Test foydalanuvchisini yaratish
Terminalda quyidagi buyruqni bering:
```bash
php artisan tinker --execute="App\Models\User::create(['name'=>'Admin','email'=>'admin@test.com','password'=>Hash::make('password'),'role'=>'admin']);"
```

### 2. Login va Token olish
Postmanda `POST` so'rovi yuboring:
- **URL**: `http://127.0.0.1:8000/api/auth/login`
- **Body (JSON)**:
  ```json
  {
      "email": "admin@test.com",
      "password": "password"
  }
  ```
- Natijada kelgan `token`ni nusxalab oling.

### 3. Autentifikatsiya (Authorization)
Keyingi so'rovlar uchun:
- Postmanda **Auth** tabiga o'ting.
- **Type**: `Bearer Token`ni tanlang.
- Tokenni joylashtiring.

### 4. Asosiy so'rovlar
- **QR yaratish**: `POST /api/qr/generate` (Body'da `office_id` yuboring).
- **Check-in**: `POST /api/attendance/check-in` (Body'da `token` yuboring).
- **Check-out**: `POST /api/attendance/check-out`.

---
**Texnologiyalar**: Laravel 11, JWT-Auth, MySQL.
