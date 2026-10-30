<?php
session_start();

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Variabel untuk menampung data yang sedang diedit
$editId = null;
$editJudul = '';

function handleRequest(&$editId, &$editJudul)
{
    // Aksi POST: Tambah Tugas ATAU Edit Tugas
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tugas'])) {
        $tugas = trim($_POST['tugas']);

        if (!empty($tugas)) {
            // Cek apakah ada input 'edit_id' yang dikirim
            if (isset($_POST['edit_id']) && $_POST['edit_id'] !== '') {
                // Logika Update / Simpan Edit
                $id = (int)$_POST['edit_id'];
                if (isset($_SESSION['tasks'][$id])) {
                    $_SESSION['tasks'][$id]['judul'] = htmlspecialchars($tugas);
                }
            } else {
                // Logika Tambah Baru
                $_SESSION['tasks'][] = [
                    'judul' => htmlspecialchars($tugas),
                    'selesai' => false
                ];
            }
        }
        redirect();
    }

    // Aksi GET: Toggle, Delete, dan Edit
    if (isset($_GET['action'])) {
        // Cek jika aksi adalah batal edit
        if ($_GET['action'] === 'cancel') {
            redirect();
        }

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            if ($_GET['action'] === 'toggle' && isset($_SESSION['tasks'][$id])) {
                $_SESSION['tasks'][$id]['selesai'] = !$_SESSION['tasks'][$id]['selesai'];
                redirect();
            } elseif ($_GET['action'] === 'delete' && isset($_SESSION['tasks'][$id])) {
                unset($_SESSION['tasks'][$id]);
                $_SESSION['tasks'] = array_values($_SESSION['tasks']);
                redirect();
            } elseif ($_GET['action'] === 'edit' && isset($_SESSION['tasks'][$id])) {
                // Aktifkan mode edit (Jangan di-redirect, biarkan merender form dengan data ini)
                $editId = $id;
                $editJudul = $_SESSION['tasks'][$id]['judul'];
            }
        }
    }
}

function redirect()
{
    header("Location: index.php");
    exit();
}

// Jalankan pemrosesan dengan melemparkan referensi variabel mode edit
handleRequest($editId, $editJudul);

function tampilkanDaftar($tasks)
{
    if (empty($tasks)) {
        echo "<p class='empty-msg'>Belum ada tugas. Tambahkan tugas pertamamu!</p>";
        return;
    }

    echo "<ul class='task-list'>";
    foreach ($tasks as $id => $task) {
        $statusClass = $task['selesai'] ? 'completed' : '';
        $checked = $task['selesai'] ? 'checked' : '';

        echo "<li class='{$statusClass}'>";
        echo "<div class='task-content'>";
        echo "<input type='checkbox' {$checked} onchange='window.location.href=\"index.php?action=toggle&id={$id}\"'>";
        echo "<span class='task-title'>" . $task['judul'] . "</span>";
        echo "</div>";
        echo "<div class='task-actions'>";
        echo "<a href='index.php?action=edit&id={$id}' class='btn-edit'>Edit</a>";
        echo "<a href='index.php?action=delete&id={$id}' class='btn-delete'>Hapus</a>";
        echo "</div>";
        echo "</li>";
    }
    echo "</ul>";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List App</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    </style>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="app-container">
        <header class="app-header">
            <h1>To-Do List</h1>
        </header>

        <main class="app-body">
            <!-- Bagian Form (Dinamis: Bisa Tambah / Edit) -->
            <section class="form-section">
                <form method="POST" action="index.php">
                    <!-- Input hidden untuk mengirim ID jika sedang dalam mode edit -->
                    <?php if ($editId !== null): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $editId; ?>">
                    <?php endif; ?>

                    <input type="text" name="tugas"
                        placeholder="Ketik tugas..."
                        value="<?php echo $editJudul; ?>"
                        required autocomplete="off" autofocus>

                    <button type="submit"><?php echo ($editId !== null) ? 'Simpan' : 'Tambah'; ?></button>

                    <!-- Tombol Batal akan muncul hanya jika sedang dalam mode edit -->
                    <?php if ($editId !== null): ?>
                        <a href="index.php?action=cancel" class="btn-cancel">Batal</a>
                    <?php endif; ?>
                </form>
            </section>

            <!-- Bagian Daftar Tugas -->
            <section class="list-section">
                <?php tampilkanDaftar($_SESSION['tasks']); ?>
            </section>
        </main>
    </div>
</body>

</html>