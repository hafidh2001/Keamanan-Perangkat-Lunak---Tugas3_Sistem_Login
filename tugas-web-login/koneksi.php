<?php
//setting default timezone
date_default_timezone_set('Asia/Jakarta');

//start session
session_start();



//membuat koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "db_keamanan_perangkat_lunak");

if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
}



//membuat function base_url
function base_url($url = null)
{
    $base_url = "http://localhost/tugas-web-login";

    if ($url != null) {
        return $base_url . "/" . $url;
    } else {
        return $base_url;
    }
}
?>

<?php
// FUNCTION LOGIN
function login($data)
{
    global $conn;

    $email = $_POST["email_user"];
    $password = $_POST["password_user"];

    $result = mysqli_query($conn, "SELECT * FROM tb_user WHERE email = '$email' ") or die(mysqli_error($conn));

    // CEK USERNAME APAKAH ADA PADA TABEL TB_REGIS_MHS
    if (mysqli_num_rows($result) === 1) {

        // CEK APAKAH PASSWORD BENAR 
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row["password"])) {

            // SET SESSION LOGIN
            $_SESSION["login"] = true;

            // SET SESSION USER
            $_SESSION["id_user"] = $row["id_user"];

            // Cek Remember
            if (isset($_POST['remember_me'])) {
                // Buat Cookie
                setcookie('id_user', $row['id_user'], time() + 86400, '/');
                setcookie('key', hash('sha256', $row['username']), time() + 86400, '/');
            }
        } else {
            return false;
        }
    }
    return mysqli_affected_rows($conn);
}





// CHECK COOKIE
function checkCookie()
{
    global $conn;

    // Cek Cookie
    if (isset($_COOKIE['id_user']) && isset($_COOKIE['key'])) {
        $id_user = $_COOKIE['id_user'];
        $key = $_COOKIE['key'];

        $result = mysqli_query($conn, "SELECT username FROM tb_user WHERE id_user = '$id_user' ");
        $row = mysqli_fetch_assoc($result);

        if ($key === hash('sha256', $row['username'])) {
            $_SESSION['login'] = true;
        }
    }
}





// FUNCTION REGISTER
function registrasi($data)
{
    global $conn;

    $username = strtolower(stripcslashes($data["username"]));
    $email = strtolower(stripcslashes($data["email_user"]));
    $password = mysqli_real_escape_string($conn, $data["password_user"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2_user"]);

    // CEK EMAIL SUDAH ADA ATAU BELUM
    $result = mysqli_query($conn, "SELECT email FROM tb_user WHERE email = '$email' ");

    // CHECK EMAIL
    if (mysqli_fetch_assoc($result)) {
        echo "<script>
		alert('Email yang dibuat sudah ada !');
		</script>";

        return false;
    }

    // CEK KONFIRMASI PASSWORD 
    if ($password !== $password2) {
        echo "<script>
		alert('Konfirmasi password salah');
		</script>";

        return false;
    }

    // ENSKRIPSI PASSWORD
    $passwordValid =  password_hash($password2, PASSWORD_DEFAULT);

    // TAMBAHKAN USER BARU KEDATABASE
    $query = "INSERT INTO tb_user(username, email, password, level) 
	VALUES('$username', '$email', '$passwordValid', 'FREE')";

    mysqli_query($conn, $query) or die(mysqli_error($conn));

    return mysqli_affected_rows($conn);
}





// MEMBUAT FUNCTION SHOW DATA (READ)
function query($query)
{
    global $conn;

    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    $boxs = [];

    // AMBIL DATA (FETCH) DARI VARIABEL RESULT DAN MASUKKAN KE ARRAY
    while ($box = mysqli_fetch_assoc($result)) {
        $boxs[] = $box;
    }
    return $boxs;
}
