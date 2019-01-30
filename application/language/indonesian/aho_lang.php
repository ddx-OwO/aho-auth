<?php
/**
 * Name:    Aho Auth
 * Author:  Dewa Andhika Putra
 *          dewaandhika18@gmail.com
 *          @dwzzzl
 *
 * Created:  2019.01.25
 *
 * Requirements: PHP 7.1 or above
 *
 * @package    aho-auth
 * @author     Dewa Andhika Putra
 * @link       http://github.com/dwzzzl/aho-auth
 * @since      Version 0.1.0
 */

defined('BASEPATH') or exit('No direct access allowed');

//Account creation
$lang['account_create_success'] = 'Akun berhasil dibuat';
$lang['account_create_failed'] = 'Akun gagal dibuat';
$lang['account_create_duplicate_identity'] = '%s telah terdaftar';
$lang['account_create_available_identity'] = '%s tersedia';
$lang['account_create_invalid_identity'] = '%s tidak valid';
$lang['account_create_min_length'] = '%s harus mempunyai minimal %d karakter';
$lang['account_create_max_length'] = '%s melebihi batas maksimal %d karakter';

// Account login/logout
$lang['account_login_success'] = 'Login berhasil';
$lang['account_login_failed'] = 'Login gagal';
$lang['account_login_not_active'] = 'Akun belum aktif';
$lang['account_logout_success'] = 'Logout berhasil';
$lang['account_logout_failed'] = 'Logout gagal';

//Account password change
$lang['account_password_change_success'] = 'Kata sandi berhasil diubah';
$lang['account_password_change_failed'] = 'Kata sandi gagal diubah';
$lang['account_forgot_password_success'] = 'Reset password berhasil. Silakan periksa email anda.';
$lang['account_forgot_password_failed'] = 'Reset password gagal';
$lang['account_forgot_username_success'] = 'Username anda telah di kirim ke email %s';
$lang['account_forgot_username_failed'] = 'Username tidak ditemukan';

//Account activation/deactivation
$lang['account_activation_success'] = 'Akun berhasil diaktifkan !';
$lang['account_activation_failed'] = 'Akun gagal diaktifkan.';
$lang['account_deactivation_success'] = 'Akun berhasil di nonaktifkan !';
$lang['account_deactivation_failed'] = 'Akun gagal di nonaktifkan.';
$lang['account_activation_email_success'] = 'Email untuk aktivasi telah dikirim.';
$lang['account_activation_email_failed'] = 'Gagal mengirim email untuk aktivasi.';
$lang['account_ban_success'] = 'Akun berhasil di banned.';
$lang['account_ban_failed'] = 'Akun gagal di banned. (%s)';
$lang['account_unban_success'] = 'Akun berhasil di unban.';
$lang['account_unban_failed'] = 'Akun gagal di unban. (%s)';

//Account changes
$lang['account_update_success'] = 'Berhasil memperbaharui informasi akun';
$lang['account_update_failed'] = 'Gagal memperbaharui informasi akun';
$lang['account_delete_success'] = 'Berhasil menghapus akun';
$lang['account_delete_failed'] = 'Gagal menghapus akun';
$lang['account_delete_protected'] = 'Akun dilindungi dan tidak dapat dihapus';

$lang['username_label'] = 'Username';
$lang['password_label'] = 'Kata sandi';
$lang['email_label'] = 'Email';
$lang['fullname_label'] = 'Nama lengkap';
$lang['identity_number_label'] = 'Nomor Identitas';
$lang['address_label'] = 'Alamat';
$lang['gender_label'] = 'Jenis Kelamin';
$lang['gender_male_label'] = 'Pria';
$lang['gender_female_label'] = 'Wanita';
$lang['birthday_label'] = 'Tanggal Lahir';
$lang['religion_label'] = 'Agama';
$lang['phone_label'] = 'Telepon';
$lang['class_label'] = 'Kelas';
$lang['school_label'] = 'Sekolah';
$lang['study_program_label'] = 'Program Studi';
$lang['forgot_password_label'] = 'Lupa kata sandi ?';
$lang['login_label'] = 'Masuk';
$lang['register_label'] = 'Daftar';
$lang['rememberme_label'] = 'Ingat saya';

// Account error
$lang['account_error_wrong_password'] = 'Password salah';
$lang['account_error_banned'] = 'Akun anda telah di <strong>banned</strong>. Hubungi Administrator untuk keterangan lebih lanjut';
$lang['account_error_unregistered'] = 'Akun tidak terdaftar';
$lang['account_error_unactivated'] = 'Akun anda belum aktif. Silakan periksa email anda atau hubungi Administrator.';
$lang['account_error_lockout'] = 'Akun anda terkunci.';
$lang['account_error_token_expired'] = 'Sesi login sudah habis';
$lang['account_error_token_invalid'] = 'Sesi login tidak diketahui';

$lang['email_activation_subject'] = 'Email aktivasi';
$lang['email_forgot_password_subject'] = 'Atur ulang kata sandi';
$lang['email_forgot_username_subject'] = 'Lupa username';
?>