<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

$servername = "localhost";
$db_username = "root";
$password = "";
$dbname = "mahasiswa_db";

$conn = new mysqli($servername, $db_username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_POST['add_mahasiswa'])) {
    $nama = $_POST['nama'];
    $nrp = $_POST['nrp'];
    $foto = $_FILES['foto']['name'];
    $tempname = $_FILES['foto']['tmp_name'];

    if ($foto && move_uploaded_file($tempname, "uploads/" . $foto)) {
        $sql = "INSERT INTO mahasiswa (nama, nrp, foto) VALUES ('$nama', '$nrp', '$foto')";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Data mahasiswa berhasil ditambahkan!</p>";
            header("Location: home.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "<p>Gagal mengunggah foto.</p>";
    }
}

if (isset($_POST['update_mahasiswa'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $nrp = $_POST['nrp'];
    $foto = $_FILES['foto']['name'];
    $tempname = $_FILES['foto']['tmp_name'];

    if ($foto) {
        $result = $conn->query("SELECT foto FROM mahasiswa WHERE id=$id");
        $row = $result->fetch_assoc();
        if ($row && file_exists("uploads/" . $row['foto'])) {
            unlink("uploads/" . $row['foto']);
        }

        if (move_uploaded_file($tempname, "uploads/" . $foto)) {
            $sql = "UPDATE mahasiswa SET nama='$nama', nrp='$nrp', foto='$foto' WHERE id=$id";
        }
    } else {
        $sql = "UPDATE mahasiswa SET nama='$nama', nrp='$nrp' WHERE id=$id";
    }

    if ($conn->query($sql) === TRUE) {
        echo "<p>Data mahasiswa berhasil diperbarui!</p>";
        header("Location: home.php");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $result = $conn->query("SELECT foto FROM mahasiswa WHERE id=$id");
    $row = $result->fetch_assoc();
    if ($row && file_exists("uploads/" . $row['foto'])) {
        unlink("uploads/" . $row['foto']);
    }

    $sql = "DELETE FROM mahasiswa WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Data mahasiswa berhasil dihapus!</p>";
        header("Location: home.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$sql = "SELECT * FROM mahasiswa";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Data</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

    <h1>Student Data</h1>

    <div style="text-align: right; margin-right: 20px;">
        <a href="logout.php" style="text-decoration: none; color: #47c9af;">Log Out</a>
    </div>

    <h2>Add Student</h2>
    <form action="home.php" method="post" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="nama" required><br>
        <label>NRP:</label>
        <input type="text" name="nrp" required><br>
        <label>Photo:</label>
        <input type="file" name="foto" required><br>
        <button type="submit" name="add_mahasiswa">Add Student</button>
    </form>

    <?php if (isset($_GET['edit_id'])):
        $id = $_GET['edit_id'];
        $result = $conn->query("SELECT * FROM mahasiswa WHERE id=$id");
        $data = $result->fetch_assoc();
    ?>
    <h2>Update Student</h2>
    <form action="home.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
        <label>Nama:</label>
        <input type="text" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required><br>
        <label>NRP:</label>
        <input type="text" name="nrp" value="<?php echo htmlspecialchars($data['nrp']); ?>" required><br>
        <label>Foto:</label>
        <input type="file" name="foto"><br>
        <small>Biarkan kosong jika tidak ingin mengganti foto.</small><br>
        <button type="submit" name="update_mahasiswa">Update Mahasiswa</button>
    </form>
    <?php endif; ?>

    <h2>Student List</h2>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>NRP</th>
            <th>Photo</th>
            <th>Action</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo htmlspecialchars($row['nrp']); ?></td>
                <td style="text-align: center;">
                    <?php if ($row['foto']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="Student Photo" style="width: 200px; height: auto; border-radius: 4px">
                        <a href="uploads/<?php echo htmlspecialchars($row['foto']); ?>" download>Download</a>
                    <?php else: ?>
                        No Photo
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <a href="home.php?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="home.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this data?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
$conn->close();
?>