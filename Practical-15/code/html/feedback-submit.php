<?php
require __DIR__.'/auth.php'; require_csrf($_POST['csrf_token']??null); require __DIR__.'/form-submit.php'; save_form('feedback');
