<title>
    <?= htmlspecialchars((!empty($pagetitle) ? "$pagetitle - " : "") . config::get("title")) ?>
</title>
<script>
    document.cookie = "timezone=" + Intl.DateTimeFormat().resolvedOptions().timeZone + "; path=/";
</script>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="styles.css">