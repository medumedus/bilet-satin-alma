<?php
require_once __DIR__ . '/../app/bootstrap.php';
logout_user();
set_flash('success', 'Çıkış yapıldı.');
redirect('/');
