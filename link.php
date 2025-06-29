<?php
// link.php - Admin page to update the popunder link

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUrl = isset($_POST['url']) ? trim($_POST['url']) : '';
    
    if (!empty($newUrl)) {
        // Validate URL format
        if (filter_var($newUrl, FILTER_VALIDATE_URL)) {
            $data = ['url' => $newUrl];
            file_put_contents('link.json', json_encode($data));
            $message = "Link updated successfully!";
        } else {
            $error = "Invalid URL format. Please enter a valid URL (e.g., https://example.com)";
        }
    } else {
        $error = "URL cannot be empty!";
    }
}

// Read current link
$currentLink = '';
if (file_exists('link.json')) {
    $data = json_decode(file_get_contents('link.json'), true);
    $currentLink = $data['url'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Popunder Link</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
        }
        .error {
            background: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Popunder Link</h1>
        
        <?php if (isset($message)): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="url">Current Popunder Link:</label>
                <input type="text" id="url" name="url" value="<?php echo htmlspecialchars($currentLink); ?>" required>
            </div>
            
            <button type="submit">Update Link</button>
        </form>
        
        <p><strong>Instructions:</strong> Enter the new URL for the popunder link. Make sure it includes the full URL (e.g., https://example.com/path?key=value)</p>
    </div>
</body>
</html>