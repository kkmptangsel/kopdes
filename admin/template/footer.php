<?php
// /koperasi/admin/template/footer.php
// Jika session_start() belum dipanggil di header.php atau file lain, 
// tambahkan di sini (atau di tempat yang lebih awal)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

    </div>

<footer class="bg-white rounded-lg shadow m-4 dark:bg-gray-800">
    <div class="w-full mx-auto max-w-screen-xl p-4 md:flex md:items-center md:justify-between">
      <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">
        © <?php echo date("Y"); ?> 
          <?php echo $SETTINGS['nama_koperasi']; ?>
      </span>
      </div>
</footer>

</div>

<div id="global-delete-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                    Konfirmasi Hapus Data
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="global-delete-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>

            <div class="p-4 md:p-5 space-y-4">
                <p class="text-gray-500 dark:text-gray-400">
                    Anda yakin ingin menghapus data **<strong id="modal-item-name-display"></strong>**?
                </p>
                <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                    <span id="modal-warning-message">Aksi ini tidak dapat dibatalkan.</span>
                </p>
            </div>
            
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <a id="confirm-delete-link" href="#" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                    Hapus
                </a>
                <button data-modal-hide="global-delete-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<div id="snackbar" class="fixed bottom-5 right-5 z-50 p-4 rounded-lg shadow-xl text-white transition-all duration-500 opacity-0 transform translate-y-full" style="min-width: 300px;">
    <span id="snackbar-message"></span>
</div>


<script>
// =============================================
// FUNGSI GLOBAL SNACKBAR (UNIVERSAL)
// =============================================
function showSnackbar(message, className) {
    const snackbar = document.getElementById('snackbar');
    const snackbarMessage = document.getElementById('snackbar-message');
    
    if (!snackbar || !snackbarMessage) return; 

    // Reset dan atur kelas/pesan
    snackbar.className = 'fixed bottom-5 right-5 z-50 p-4 rounded-lg shadow-xl text-white transition-all duration-500 opacity-0 transform translate-y-full ' + className;
    snackbarMessage.innerHTML = message;
    
    // Tampilkan Snackbar
    setTimeout(() => {
        snackbar.classList.remove('opacity-0', 'translate-y-full');
        snackbar.classList.add('opacity-100', 'translate-y-0');
    }, 50);

    // Sembunyikan Snackbar setelah 4 detik
    setTimeout(() => {
        snackbar.classList.remove('opacity-100', 'translate-y-0');
        snackbar.classList.add('opacity-0', 'translate-y-full');
    }, 4000);
}


// =============================================
// 1. LOGIKA SNACKBAR DARI PHP REDIRECT
// =============================================
document.addEventListener('DOMContentLoaded', () => {
    // Ambil pesan dari SESSION PHP
    const successMessage = "<?php echo isset($_SESSION['notif_sukses']) ? htmlspecialchars($_SESSION['notif_sukses']) : ''; ?>";
    const errorMessage = "<?php echo isset($_SESSION['notif_error']) ? htmlspecialchars($_SESSION['notif_error']) : ''; ?>";
    
    // Hapus sesi setelah diambil (PENTING untuk Flash Message)
    <?php unset($_SESSION['notif_sukses'], $_SESSION['notif_error']); ?>

    if (successMessage) {
        showSnackbar(successMessage, 'bg-green-600');
    } else if (errorMessage) {
        showSnackbar(errorMessage, 'bg-red-600');
    }

// =============================================
// 2. LOGIKA AJAX UNTUK TOGGLE STATUS (Tidak ada perubahan)
// =============================================
    const switches = document.querySelectorAll('.toggle-status-switch');
    const currentUrl = new URL(window.location.href);
    const statusFilter = currentUrl.searchParams.get('status') || 'aktif';

    switches.forEach(switchElement => {
        switchElement.addEventListener('change', function() {
            const anggotaId = this.getAttribute('data-anggota-id');
            const isChecked = this.checked;
            const statusTextElement = this.closest('label').querySelector('span');
            
            const payload = {
                id: anggotaId,
                status: isChecked
            };

            this.disabled = true;

            fetch('ubah_status_anggota.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal menghubungi server. Status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                this.disabled = false;

                if (data.success) {
                    const newStatusDisplay = isChecked ? 'Aktif' : 'Non-aktif';
                    statusTextElement.textContent = newStatusDisplay;
                    
                    showSnackbar(data.message, 'bg-green-600'); 
                    
                    // Logika Filtering Tampilan (Hapus baris jika tidak sesuai filter)
                    if (statusFilter === 'aktif' && !isChecked) {
                        this.closest('tr').remove(); 
                    } 
                    else if (statusFilter === 'non-aktif' && isChecked) {
                        this.closest('tr').remove(); 
                    }

                } else {
                    this.checked = !isChecked; 
                    showSnackbar(data.message, 'bg-red-600');
                }
            })
            .catch(error => {
                this.disabled = false;
                this.checked = !isChecked;
                showSnackbar(`Terjadi kesalahan: ${error.message}`, 'bg-red-600');
            });
        });
    });

// =============================================
// 3. GLOBAL DELETE MODAL LOGIC (Tidak ada perubahan)
// =============================================
    const deleteButtons = document.querySelectorAll('.btn-delete-modal');
    const confirmDeleteLink = document.getElementById('confirm-delete-link');
    const modalItemNameDisplay = document.getElementById('modal-item-name-display');
    const modalWarningMessage = document.getElementById('modal-warning-message');
    const deleteModal = document.getElementById('global-delete-modal');

    if (deleteButtons.length > 0 && deleteModal) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const itemName = this.getAttribute('data-item-name');
                const deleteUrl = this.getAttribute('data-delete-url');
                const warningMsg = this.getAttribute('data-warning-msg') || 'Aksi ini tidak dapat dibatalkan.';
                
                modalItemNameDisplay.textContent = itemName || 'ini'; 
                modalWarningMessage.textContent = warningMsg;
                confirmDeleteLink.href = deleteUrl + itemId;
            });
        });
    }

}); // Akhir DOMContentLoaded
</script>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</body>
</html>