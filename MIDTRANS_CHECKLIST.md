# ✅ CHECKLIST: Setup Midtrans Payment

## 🎯 Quick Start - YANG HARUS DILAKUKAN:

### 1️⃣ Dapatkan Credentials Midtrans

- [ ] Login ke https://dashboard.sandbox.midtrans.com/
- [ ] Copy **Server Key**: SB-Mid-server-xxxxxx
- [ ] Copy **Client Key**: SB-Mid-client-xxxxxx

### 2️⃣ Update Backend `.env`

Edit file: `backend/.env`

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-PASTE_YOUR_SERVER_KEY_HERE
MIDTRANS_CLIENT_KEY=SB-Mid-client-PASTE_YOUR_CLIENT_KEY_HERE
MIDTRANS_IS_PRODUCTION=false
```

### 3️⃣ Update Frontend `index.html`

Edit file: `frontend/index.html` (line 9)

Ganti:

```html
data-client-key="SB-Mid-client-YOUR_CLIENT_KEY"
```

Jadi:

```html
data-client-key="SB-Mid-client-PASTE_YOUR_CLIENT_KEY_HERE"
```

### 4️⃣ Setup Notification URL

- [ ] Login Midtrans Dashboard
- [ ] Settings → Configuration
- [ ] Set **Payment Notification URL**:
  ```
  http://localhost:8000/api/guest/midtrans/notification
  ```

### 5️⃣ Test Payment

- [ ] Jalankan backend: `cd backend && php artisan serve`
- [ ] Jalankan frontend: `cd frontend && npm run dev`
- [ ] Buat Reservasi (harus online payment)
- [ ] Gunakan test card: **4811 1111 1111 1114**

---

## 📋 Files Modified

### Backend:

- ✅ `backend/.env` - Midtrans config
- ✅ `backend/config/midtrans.php` - Config file
- ✅ `backend/app/Http/Controllers/Api/MidtransController.php` - Payment controller
- ✅ `backend/routes/api.php` - Added Midtrans routes

### Frontend:

- ✅ `frontend/index.html` - Added Snap script
- ✅ `frontend/src/views/Payment.vue` - Conditional payment methods
- ✅ `frontend/src/views/PaymentConfirmation.vue` - Midtrans integration

---

## 🎯 Payment Rules

| Order Type    | Cash          | Online (QRIS) |
| ------------- | ------------- | ------------- |
| **Reservasi** | ❌ Tidak bisa | ✅ Wajib      |
| **Dine In**   | ✅ Bisa       | ✅ Bisa       |

---

## 🧪 Test Cards (Sandbox)

| Card Number         | CVV | Exp   | Result     |
| ------------------- | --- | ----- | ---------- |
| 4811 1111 1111 1114 | 123 | 01/25 | ✅ Success |
| 4911 1111 1111 1113 | 123 | 01/25 | ⏳ Pending |
| 4411 1111 1111 1118 | 123 | 01/25 | ❌ Failed  |

---

## 📞 Need Help?

Lihat file lengkap: `MIDTRANS_SETUP.md`
