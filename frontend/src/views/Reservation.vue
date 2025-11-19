<template>
  <div class="bg-white min-h-screen">
    <div class="max-w-md mx-auto bg-white min-h-screen font-sans flex flex-col">

      <!-- Header -->
      <header class="bg-white border-b border-gray-200 px-4 py-4">
        <h1 class="text-xl font-bold text-gray-900 text-center">Reservation</h1>
      </header>

      <!-- Main Content -->
      <main class="flex-1 px-6 py-6 overflow-y-auto bg-white">
        
        <!-- Title Section -->
        <section class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Reserve Your Table</h2>
          <p class="text-sm text-gray-500 leading-relaxed">Please fill in the form below to make a reservation.</p>
        </section>

        <!-- Form Section -->
        <section class="space-y-5">
          
          <!-- Nama Lengkap -->
          <div>
            <label class="text-sm font-semibold text-gray-700 mb-2 block">Nama Lengkap</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
              </div>
              <input 
                v-model="form.name" 
                type="text" 
                placeholder="Masukkan nama Anda"
                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Nomor Telepon -->
          <div>
            <label class="text-sm font-semibold text-gray-700 mb-2 block">Nomor Telepon</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                </svg>
              </div>
              <input 
                v-model="form.phone" 
                type="tel" 
                placeholder="Masukkan nomor telepon"
                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Tanggal & Jam -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-semibold text-gray-700 mb-2 block">Tanggal</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <input 
                  v-model="form.date" 
                  type="date" 
                  placeholder="mm/dd/yyyy"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all"
                />
              </div>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700 mb-2 block">Jam</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <input 
                  v-model="form.time" 
                  type="time" 
                  placeholder="--:-- --"
                  class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all"
                />
              </div>
            </div>
          </div>

          <!-- Jumlah Orang -->
          <div>
            <label class="text-sm font-semibold text-gray-700 mb-2 block">Jumlah Orang</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                </svg>
              </div>
              <input 
                v-model="form.people" 
                type="text" 
                placeholder="Jumlah orang yang akan hadir"
                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-100 transition-all placeholder:text-gray-400"
              />
            </div>
          </div>

        </section>

      </main>

      <!-- Footer Button -->
      <footer class="bg-white border-t border-gray-200 pb-6 pt-4 px-6">
        <button 
          @click="submitReservation"
          class="w-full py-4 text-white text-base font-bold rounded-xl transition-all shadow-lg active:scale-[0.98]"
          style="background-color: #B85814;"
          onmouseover="this.style.backgroundColor='#A04D12'" 
          onmouseout="this.style.backgroundColor='#B85814'"
        >
          Buat Reservasi
        </button>
      </footer>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref({
  name: '',
  phone: '',
  date: '',
  time: '',
  people: ''
});

const submitReservation = () => {
  // Validasi
  if (!form.value.name || !form.value.phone || !form.value.date || !form.value.time || !form.value.people) {
    alert('Mohon lengkapi semua data reservasi.');
    return;
  }

  // Set Tipe Order ke 'Reservasi'
  localStorage.setItem('orderType', 'Reservasi');
  
  // Simpan detail reservasi
  localStorage.setItem('reservationDetails', JSON.stringify(form.value));
  
  // Bersihkan cart lama jika ada
  localStorage.removeItem('cart');
  localStorage.removeItem('cartNotes');

  // Redirect ke Menu
  router.push('/user/menu');
};
</script>

<style scoped>
main {
  overflow-y: auto;
}

input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
  opacity: 0;
  position: absolute;
  right: 12px;
  cursor: pointer;
}
</style>