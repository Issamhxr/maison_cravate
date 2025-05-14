<!DOCTYPE html>
<html>
<head>
    <title>Test Links</title>
</head>
<body>
    <h1>Test Links</h1>
    <ul>
        <li><a href="products/list.php">Test 1: products/list.php</a></li>
        <li><a href="./products/list.php">Test 2: ./products/list.php</a></li>
        <li><a href="../products/list.php">Test 3: ../products/list.php</a></li>
        <li><a href="/products/list.php">Test 4: /products/list.php</a></li>
        <li><a href="/maison_cravate/products/list.php">Test 5: /maison_cravate/products/list.php</a></li>
    </ul>
    
    <p>Current directory: <?php echo __DIR__; ?></p>
    <p>Document root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
    <p>Script filename: <?php echo $_SERVER['SCRIPT_FILENAME']; ?></p>
    <p>PHP self: <?php echo $_SERVER['PHP_SELF']; ?></p>
</body>
</html> 