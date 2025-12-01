# 🔐 Midtrans Sandbox Configuration Guide

## 📋 Langkah Setup Midtrans Sandbox

### 1. **Dapatkan Credentials dari Midtrans Dashboard**

Login ke [Midtrans Sandbox Dashboard](https://dashboard.sandbox.midtrans.com/)

Ambil credentials:

- **Server Key**: SB-Mid-server-xxxxxx
- **Client Key**: SB-Mid-client-xxxxxx

### 2. **Update Backend Configuration**

Edit file `backend/.env`:

```env
# Midtrans Configuration
MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_ACTUAL_SERVER_KEY
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_ACTUAL_CLIENT_KEY
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

**⚠️ IMPORTANT**: Replace `YOUR_ACTUAL_SERVER_KEY` and `YOUR_ACTUAL_CLIENT_KEY` dengan credentials asli dari Midtrans Dashboard!

### 3. **Update Frontend Configuration**

Edit file `frontend/index.html`, update Client Key di Snap script:

```html
<!-- Midtrans Snap -->
<script
  type="text/javascript"
  src="https://app.sandbox.midtrans.com/snap/snap.js"
  data-client-key="SB-Mid-client-YOUR_ACTUAL_CLIENT_KEY"
></script>
```

**⚠️ IMPORTANT**: Replace `YOUR_ACTUAL_CLIENT_KEY` dengan Client Key asli!

### 4. **Setup Notification URL di Midtrans Dashboard**

Login ke Midtrans Dashboard → Settings → Configuration

Set **Payment Notification URL**:

```
http://localhost:8000/api/guest/midtrans/notification
```

Untuk production nanti, ganti dengan domain asli:

```
https://yourdomain.com/api/guest/midtrans/notification
```

### 5. **Test Pembayaran**

#### Test Cards (Sandbox Only):

- **Success**: 4811 1111 1111 1114
- **Pending**: 4911 1111 1111 1113
- **Failed**: 4411 1111 1111 1118

CVV: 123
Exp: 01/2025

#### Flow Testing:

**A. Reservasi (Online Payment Only)**

1. Pilih Order Type: Reservasi
2. Isi data customer & pilih meja
3. Tambah items ke cart
4. Payment → HANYA QRIS/Online tersedia
5. Klik Pay → Popup Midtrans muncul
6. Pilih payment method di Midtrans
7. Selesaikan pembayaran

**B. Dine In (Cash atau Online)**

1. Pilih Order Type: Dine In
2. Isi data customer & pilih meja
3. Tambah items ke cart
4. Payment → Bisa pilih Cash atau Online
5. Cash → langsung success
6. Online → popup Midtrans muncul

---

## 🔄 Payment Flow

### Customer Flow (Frontend)

```
1. PaymentConfirmation.vue submit order
   ↓
2. Backend create Order + Payment (status: pending)
   ↓
3. Frontend request Snap Token
   ↓
4. Midtrans Snap popup muncul
   ↓
5. Customer bayar di Midtrans
   ↓
6. Midtrans kirim notification ke backend
   ↓
7. Backend update Payment & Order status
   ↓
8. Frontend redirect ke Success page
```

### Backend Flow

```
1. OrderController.submitOrder()
   - Create Order (status: pending)
   - Create Payment (status: pending)

2. MidtransController.createSnapToken()
   - Generate Snap Token
   - Return to frontend

3. MidtransController.notification()
   - Receive notification from Midtrans
   - Update Payment status
   - Update Order status
   - Update Table status (jika Dine In)
```

---

## 📝 Database Payment Status

### Payment Table:

- `status`: 'pending', 'paid', 'failed'
- `payment_type`: 'cash', 'qris', 'credit_card', 'bank_transfer', dll
- `method`: 'cash', 'midtrans'
- `transaction_id`: ID dari Midtrans (untuk online payment)

### Order Table:

- `status`: 'pending', 'confirmed', 'processing', 'completed', 'cancelled'

---

## 🎯 Rules

### Reservasi:

- ✅ **WAJIB** online payment (QRIS/Midtrans)
- ❌ **TIDAK BISA** cash payment
- Meja tetap `available` sampai hari H

### Dine In:

- ✅ Bisa cash payment
- ✅ Bisa online payment
- Meja langsung jadi `occupied` setelah payment success

---

## 🐛 Troubleshooting

### 1. Popup Midtrans tidak muncul

- Cek `window.snap` di console
- Pastikan Snap script sudah load (lihat Network tab)
- Cek Client Key sudah benar di `index.html`

### 2. Payment notification tidak diterima

- Cek URL notification di Midtrans Dashboard
- Cek log Laravel: `backend/storage/logs/laravel.log`
- Test manual dengan Postman ke `/api/guest/midtrans/notification`

### 3. Payment status tidak update

- Cek `MidtransController.notification()`
- Cek database `payments` table
- Cek response dari Midtrans

---

## 📚 API Endpoints

### Guest/Customer:

```
POST /api/guest/midtrans/create-snap-token
Body: { "order_id": "OR0001" }
Response: { "snap_token": "xxx", "order_id": "OR0001" }

POST /api/guest/midtrans/notification
Body: (otomatis dari Midtrans)

GET /api/guest/midtrans/check-status?order_id=OR0001
Response: { "status": "paid", "payment_type": "qris" }
```

---

## 🔐 Security Notes

⚠️ **JANGAN COMMIT** file `.env` ke Git!
⚠️ **JANGAN EXPOSE** Server Key di frontend!
⚠️ **GUNAKAN HTTPS** untuk production!

---

## 📞 Support

- Midtrans Docs: https://docs.midtrans.com/
- Midtrans Sandbox: https://dashboard.sandbox.midtrans.com/
- Payment Link Sandbox: https://app.sandbox.midtrans.com/payment-links/

---

Last updated: December 2025
