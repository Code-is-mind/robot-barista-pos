<?php
/**
 * Root Index - Redirect to Public Folder
 * This file redirects all root access to the public directory
 */

// Redirect to public folder
header('Location: public/index.php');
exit;
