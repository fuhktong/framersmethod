<?php
// Clean the request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = rtrim($request_uri, '/');

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
    '/contact-us' => 'contact',
    '/data' => 'data',
    '/data/appendix-a' => 'data-appendix-a',
    '/data/appendix-b' => 'data-appendix-b',
    '/data/appendix-c' => 'data-appendix-c',
    '/data/appendix-d' => 'data-appendix-d',
    '/data/appendix-e' => 'data-appendix-e',
    '/data/appendix-f' => 'data-appendix-f',
    '/data/appendix-g' => 'data-appendix-g',
    '/data/appendix-h' => 'data-appendix-h',
    '/data/appendix-i' => 'data-appendix-i',
    '/data/appendix-j' => 'data-appendix-j',
    '/data/appendix-k' => 'data-appendix-k',
    '/data/appendix-l' => 'data-appendix-l',
    '/data/appendix-m' => 'data-appendix-m',
    '/data/appendix-n' => 'data-appendix-n',
    '/data/appendix-o' => 'data-appendix-o',
    '/data/appendix-p' => 'data-appendix-p'
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
    'contact' => ['title' => 'Contact - The Framers Method', 'description' => 'Contact the Framers Method team'],
    'data' => ['title' => 'Book Data - The Framers Method', 'description' => 'Appendices and data from On the Framers\' Electoral College'],
    'data-appendix-a' => ['title' => 'Appendix A - The Framers Method', 'description' => 'Collected Notes from Convention Debates Concerning Methods to Choose the President'],
    'data-appendix-b' => ['title' => 'Appendix B - The Framers Method', 'description' => 'All Advocation and Opposition Debate Data Concerning the Various Methods to Choose the President'],
    'data-appendix-c' => ['title' => 'Appendix C - The Framers Method', 'description' => 'All Votes Concerning the Various Methods to Choose the President'],
    'data-appendix-d' => ['title' => 'Appendix D - The Framers Method', 'description' => 'All Voting Data Concerning the Various Methods to Choose the President: Large States versus Small States'],
    'data-appendix-e' => ['title' => 'Appendix E - The Framers Method', 'description' => 'All Voting Data Concerning the Various Methods to Choose the President: Northern States versus Southern States'],
    'data-appendix-f' => ['title' => 'Appendix F - The Framers Method', 'description' => 'Votes per Electoral Vote (VPEV) in Presidential Elections: 1992 - 2020'],
    'data-appendix-g' => ['title' => 'Appendix G - The Framers Method', 'description' => 'Party State Wins by Size in Presidential Elections: 1992 - 2020'],
    'data-appendix-h' => ['title' => 'Appendix H - The Framers Method', 'description' => 'Voter Turnout Performance in Presidential Elections: 2000 - 2020'],
    'data-appendix-i' => ['title' => 'Appendix I - The Framers Method', 'description' => 'Voter Turnout Performance Charts in Elections: 2000 - 2020'],
    'data-appendix-j' => ['title' => 'Appendix J - The Framers Method', 'description' => 'Potential Additional Votes in the 2000 and 2016 Elections'],
    'data-appendix-k' => ['title' => 'Appendix K - The Framers Method', 'description' => 'Electoral College Total Wasted Votes in Presidential Elections: 1992 to 2020'],
    'data-appendix-l' => ['title' => 'Appendix L - The Framers Method', 'description' => 'Hamilton Method Lost Votes in Presidential Elections: 1992 to 2020'],
    'data-appendix-m' => ['title' => 'Appendix M - The Framers Method', 'description' => 'Hamilton Method Surplus Votes in Presidential Elections: 1992 to 2020'],
    'data-appendix-n' => ['title' => 'Appendix N - The Framers Method', 'description' => 'Hamilton Method Total Wasted Votes in Presidential Elections: 1992 to 2020'],
    'data-appendix-o' => ['title' => 'Appendix O - The Framers Method', 'description' => 'Popular Vote Total Wasted Votes in Presidential Elections: 1992 to 2020'],
    'data-appendix-p' => ['title' => 'Appendix P - The Framers Method', 'description' => 'Percentage Comparison of the Electoral College, Popular Vote, and Hamilton Method in Presidential Elections: 1992 to 2020']
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
        'contact' => ['/contact/contact.css', '/contact/contactform.css'],
        'data' => ['/data/data.css'],
        'data-appendix-a' => ['/data/data.css'],
        'data-appendix-b' => ['/data/data.css'],
        'data-appendix-c' => ['/data/data.css'],
        'data-appendix-d' => ['/data/data.css'],
        'data-appendix-e' => ['/data/data.css'],
        'data-appendix-f' => ['/data/data.css'],
        'data-appendix-g' => ['/data/data.css'],
        'data-appendix-h' => ['/data/data.css'],
        'data-appendix-i' => ['/data/data.css'],
        'data-appendix-j' => ['/data/data.css'],
        'data-appendix-k' => ['/data/data.css'],
        'data-appendix-l' => ['/data/data.css'],
        'data-appendix-m' => ['/data/data.css'],
        'data-appendix-n' => ['/data/data.css'],
        'data-appendix-o' => ['/data/data.css'],
        'data-appendix-p' => ['/data/data.css']
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
            } elseif ($current_page === 'data') {
                include 'data/data.php';
            } elseif ($current_page === 'data-appendix-a') {
                include 'data/appendix-a.php';
            } elseif ($current_page === 'data-appendix-b') {
                include 'data/appendix-b.php';
            } elseif ($current_page === 'data-appendix-c') {
                include 'data/appendix-c.php';
            } elseif ($current_page === 'data-appendix-d') {
                include 'data/appendix-d.php';
            } elseif ($current_page === 'data-appendix-e') {
                include 'data/appendix-e.php';
            } elseif ($current_page === 'data-appendix-f') {
                include 'data/appendix-f.php';
            } elseif ($current_page === 'data-appendix-g') {
                include 'data/appendix-g.php';
            } elseif ($current_page === 'data-appendix-h') {
                include 'data/appendix-h.php';
            } elseif ($current_page === 'data-appendix-i') {
                include 'data/appendix-i.php';
            } elseif ($current_page === 'data-appendix-j') {
                include 'data/appendix-j.php';
            } elseif ($current_page === 'data-appendix-k') {
                include 'data/appendix-k.php';
            } elseif ($current_page === 'data-appendix-l') {
                include 'data/appendix-l.php';
            } elseif ($current_page === 'data-appendix-m') {
                include 'data/appendix-m.php';
            } elseif ($current_page === 'data-appendix-n') {
                include 'data/appendix-n.php';
            } elseif ($current_page === 'data-appendix-o') {
                include 'data/appendix-o.php';
            } elseif ($current_page === 'data-appendix-p') {
                include 'data/appendix-p.php';
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
    
    <?php if ($current_page === 'team'): ?>
    <!-- Team Modal Enhancement -->
    <script src="/login/team-modal.js"></script>
    <?php endif; ?>
</body>
</html>