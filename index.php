<?php
// Clean the request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = rtrim($request_uri, '/');

// Handle static files
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf)$/', $request_uri)) {
    return false;
}

// Define routes
$routes = [
    '' => 'home',
    '/' => 'home',
    '/general-caucus' => 'general-caucus',
    '/how-it-works' => 'how-it-works',
    '/national-elections' => 'national-elections',
    '/democracy-vs-republic' => 'democracy-vs-republic',
    '/hamilton-method' => 'hamilton-method',
    '/electors-convention' => 'electors-convention',
    '/book' => 'book',
    '/faq' => 'faq',
    '/contribute' => 'contribute',
    '/team' => 'team',
    '/contact-us' => 'contact'
];

// Get current page
$current_page = $routes[$request_uri] ?? 'home';

// Set page title and meta data
$page_data = [
    'home' => ['title' => 'The Framers Method', 'description' => 'A new approach to American democracy'],
    'general-caucus' => ['title' => 'General Caucus - The Framers Method', 'description' => 'Learn about the General Caucus system'],
    'how-it-works' => ['title' => 'How It Works - The Framers Method', 'description' => 'Understanding how the Framers Method works'],
    'national-elections' => ['title' => 'National Elections - The Framers Method', 'description' => 'National elections in the Framers Method'],
    'democracy-vs-republic' => ['title' => 'Democracy vs Republic - The Framers Method', 'description' => 'Understanding the difference between democracy and republic'],
    'hamilton-method' => ['title' => 'Hamilton Method - The Framers Method', 'description' => 'Learn about the Hamilton Method'],
    'electors-convention' => ['title' => 'Electors Convention - The Framers Method', 'description' => 'The Electors Convention system'],
    'book' => ['title' => 'Book - The Framers Method', 'description' => 'Get the Framers Method book'],
    'faq' => ['title' => 'FAQ - The Framers Method', 'description' => 'Frequently asked questions'],
    'contribute' => ['title' => 'Contribute - The Framers Method', 'description' => 'How to contribute to the Framers Method'],
    'team' => ['title' => 'Team - The Framers Method', 'description' => 'Meet the Framers Method team'],
    'contact' => ['title' => 'Contact - The Framers Method', 'description' => 'Contact the Framers Method team']
];

$page_title = $page_data[$current_page]['title'] ?? 'The Framers Method';
$page_description = $page_data[$current_page]['description'] ?? 'A new approach to American democracy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>" />
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico" />
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png" />
    <link rel="manifest" href="/manifest.json" />
    <meta name="theme-color" content="#000000" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://framersmethod.com<?php echo $_SERVER['REQUEST_URI']; ?>" />
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta property="og:image" content="https://framersmethod.com/images/og-image.png" />
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://framersmethod.com<?php echo $_SERVER['REQUEST_URI']; ?>" />
    <meta property="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>" />
    <meta property="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta property="twitter:image" content="https://framersmethod.com/images/og-image.png" />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/styles.css" />
    <link rel="stylesheet" href="/header/header.css" />
    <link rel="stylesheet" href="/header/navbar.css" />
    <link rel="stylesheet" href="/header/toggle.css" />
    <link rel="stylesheet" href="/footer/footer.css" />
    <link rel="stylesheet" href="/socialmediabar/socialmediabar.css" />
    
    <?php
    // Include page-specific CSS
    $css_files = [
        'home' => ['/pages/home.css'],
        'general-caucus' => ['/pages/general-caucus.css'],
        'how-it-works' => ['/pages/how-it-works.css'],
        'national-elections' => ['/pages/national-elections.css'],
        'democracy-vs-republic' => ['/pages/democracy-vs-republic.css'],
        'hamilton-method' => ['/pages/hamilton-method.css'],
        'electors-convention' => ['/pages/electors-convention.css'],
        'book' => ['/pages/book.css'],
        'faq' => ['/pages/faq.css'],
        'contribute' => ['/pages/contribute.css'],
        'team' => ['/pages/team.css'],
        'contact' => ['/contact/contact.css', '/contact/contactform.css']
    ];
    
    if (isset($css_files[$current_page])) {
        foreach ($css_files[$current_page] as $css_file) {
            echo '<link rel="stylesheet" href="' . $css_file . '" />' . "\n    ";
        }
    }
    ?>
</head>
<body>
    <div class="app">
        <?php include 'header/header.php'; ?>
        
        <main>
            <?php
            if ($current_page === 'contact') {
                include 'contact/contact.php';
            } else {
                $page_file = "pages/{$current_page}.php";
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    include 'pages/home.php';
                }
            }
            ?>
        </main>
        
        <?php include 'footer/footer.php'; ?>
    </div>
    
    <!-- JavaScript -->
    <script src="/scrolltotop/scrolltotop.js"></script>
</body>
</html>