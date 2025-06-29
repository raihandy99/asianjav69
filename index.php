<?php
// AVDBAPI Configuration
define('AVDBAPI_URL', 'https://avdbapi.com/api.php/provide1/vod/');
define('DEFAULT_CATEGORY', 1); // Censored category as default

// Get current page and parameters
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$category_id = isset($_GET['t']) ? intval($_GET['t']) : DEFAULT_CATEGORY;
$search_query = isset($_GET['wd']) ? urlencode(trim($_GET['wd'])) : '';
$video_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Function to fetch data from API
function fetch_api_data($params = []) {
    $default_params = [
        'ac' => 'detail',
        'pg' => 1,
        't' => 0, // 0 means all categories
        'wd' => '',
        'ids' => ''
    ];

    $params = array_merge($default_params, $params);
    $query_string = http_build_query($params);
    $api_url = AVDBAPI_URL . '?' . $query_string;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Referer: https://avdbapi.com/',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log('CURL Error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    if ($http_code != 200) {
        error_log("API Request Failed. HTTP Code: $http_code");
        return false;
    }

    return json_decode($response, true);
}

// Get video data based on request
if ($video_id > 0) {
    $video_data = fetch_api_data(['ac' => 'videolist', 'ids' => $video_id]);
    $video = $video_data['list'][0] ?? null;
    $recommended = fetch_api_data(['pg' => 1]); // Get recommended from all categories
} elseif (!empty($search_query)) {
    $video_data = fetch_api_data(['wd' => $search_query, 'pg' => $current_page]);
    $videos = $video_data['list'] ?? [];
    $total = $video_data['total'] ?? 0;
    $pagecount = $video_data['pagecount'] ?? 1;
} else {
    // If no category selected, show all videos
    $params = ['pg' => $current_page];
    if ($category_id > 0) {
        $params['t'] = $category_id;
    }
    
    $video_data = fetch_api_data($params);
    $videos = $video_data['list'] ?? [];
    $total = $video_data['total'] ?? 0;
    $pagecount = $video_data['pagecount'] ?? 1;
}

// Category list
$categories = [
    0 => 'All Videos',
    1 => 'Censored',
    2 => 'Uncensored',
    3 => 'Uncensored Leaked',
    4 => 'Amateur',
    5 => 'Chinese AV',
    6 => 'Western'
];

// Handle API errors
if ($video_data === false) {
    $error_message = "Failed to fetch data from API. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($video) ? htmlspecialchars($video['vod_name']) : 'AVDBAPI Video Site'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      /* Netflix-Style CSS for AVDBAPI Video Site */

:root {
    --primary-bg: #141414;
    --secondary-bg: #181818;
    --hover-bg: #232323;
    --text-primary: #ffffff;
    --text-secondary: #b3b3b3;
    --accent-red: #e50914;
    --accent-hover: #f40612;
    --card-bg: #232323;
    --border-color: #333333;
    --card-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    --transition-speed: 0.3s;
}

body {
    background-color: var(--primary-bg);
    color: var(--text-primary);
    font-family: 'Helvetica Neue', Arial, sans-serif;
    margin: 0;
    padding: 0;
    line-height: 1.5;
}

/* Navbar Styling */
nav.navbar {
    background-color: var(--primary-bg);
    padding: 1rem 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: background-color 0.3s ease;
}

nav.navbar.scrolled {
    background-color: rgba(0, 0, 0, 0.9);
}

.navbar-brand {
    color: var(--accent-red) !important;
    font-size: 1.5rem;
    font-weight: bold;
    transition: transform var(--transition-speed);
}

.navbar-brand:hover {
    transform: scale(1.05);
}

.navbar-dark .navbar-nav .nav-link {
    color: var(--text-secondary);
    font-weight: 500;
    transition: color var(--transition-speed);
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border-radius: 4px;
}

.navbar-dark .navbar-nav .nav-link:hover,
.navbar-dark .navbar-nav .nav-link.active {
    color: var(--text-primary);
    background-color: rgba(255, 255, 255, 0.1);
}

.navbar-dark .navbar-toggler {
    border-color: rgba(255, 255, 255, 0.2);
}

.search-box {
    position: relative;
    width: 300px;
    max-width: 100%;
}

.search-box .form-control {
    background-color: rgba(255, 255, 255, 0.2);
    border: none;
    color: var(--text-primary);
    padding-right: 40px;
    transition: background-color var(--transition-speed);
    border-radius: 4px;
}

.search-box .form-control:focus {
    background-color: rgba(255, 255, 255, 0.3);
    box-shadow: none;
}

.search-box .btn {
    position: absolute;
    right: 0;
    top: 0;
    border: none;
    background: transparent;
}

.search-box .btn:hover {
    color: var(--accent-red);
}

/* Content Wrapper */
.content-wrapper {
    padding-top: 20px;
    padding-bottom: 60px;
    min-height: calc(100vh - 180px);
}

/* Section Titles */
.section-title {
    color: var(--text-primary);
    margin-bottom: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.section-title i {
    color: var(--accent-red);
}

/* Video Cards */
.video-card {
    background-color: var(--card-bg);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 30px;
    cursor: pointer;
    position: relative;
    transition: transform var(--transition-speed), box-shadow var(--transition-speed);
    box-shadow: var(--card-shadow);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.video-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
    z-index: 10;
}

.video-thumbnail {
    width: 100%;
    aspect-ratio: 2/3;
    object-fit: cover;
    transition: opacity var(--transition-speed);
}

.video-card:hover .video-thumbnail {
    opacity: 0.7;
}

.card-body {
    padding: 0.875rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.card-title {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    line-height: 1.4;
    font-weight: 500;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    flex-grow: 1;
}

.card-title a {
    color: var(--text-primary);
    text-decoration: none !important;
}

.card-title a:hover {
    color: var(--accent-red);
}

.badge {
    background-color: var(--accent-red);
    font-weight: 500;
    padding: 0.35em 0.65em;
}

/* Video Player Section */
.player-section {
    background-color: var(--secondary-bg);
    border-radius: 6px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}

.player-container {
    width: 100%;
    background-color: black;
}

.video-info {
    padding: 1.5rem;
}

.video-info h1 {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
}

/* Details Card */
.details-card {
    background-color: var(--card-bg);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 1.5rem;
    box-shadow: var(--card-shadow);
    border: 1px solid var(--border-color);
}

.details-card .card-header {
    padding: 1rem 1.25rem;
    background-color: var(--secondary-bg);
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
}

.details-card .card-body {
    padding: 1.25rem;
}

.details-card .list-group-item {
    background-color: var(--card-bg);
    border-color: var(--border-color);
    color: var(--text-secondary);
    padding: 0.75rem 1.25rem;
}

.details-card .list-group-item strong {
    color: var(--text-primary);
    margin-right: 0.5rem;
}

/* Cover Image */
.cover-image {
    width: 100%;
    border-radius: 6px;
    box-shadow: var(--card-shadow);
}

/* Download Links */
.btn-outline-primary {
    color: var(--text-primary);
    border-color: var(--accent-red);
    background: transparent;
    transition: all var(--transition-speed);
}

.btn-outline-primary:hover, 
.btn-outline-primary:focus {
    background-color: var(--accent-red);
    border-color: var(--accent-red);
    color: white;
    transform: translateY(-2px);
}

/* Pagination */
.pagination {
    margin-top: 2rem;
}

.pagination .page-link {
    color: var(--text-primary);
    background-color: var(--card-bg);
    border-color: var(--border-color);
    margin: 0 3px;
    border-radius: 4px;
    transition: all var(--transition-speed);
}

.pagination .page-link:hover {
    background-color: var(--hover-bg);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.pagination .page-item.active .page-link {
    background-color: var(--accent-red);
    border-color: var(--accent-red);
}

.pagination .page-item.disabled .page-link {
    color: var(--text-secondary);
    background-color: var(--secondary-bg);
    border-color: var(--border-color);
}

/* Footer */
footer {
    background-color: var(--secondary-bg);
    padding: 2rem 0;
    margin-top: 2rem;
    border-top: 1px solid var(--border-color);
}

footer h5 {
    color: var(--text-primary);
    margin-bottom: 1rem;
}

footer h5 i {
    color: var(--accent-red);
}

/* Alert Styling */
.alert {
    border-radius: 4px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border: none;
}

.alert-secondary {
    background-color: var(--secondary-bg);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.alert-info {
    background-color: rgba(13, 202, 240, 0.15);
    color: #8be0f9;
}

.alert-danger {
    background-color: rgba(229, 9, 20, 0.15);
    color: #ff6b78;
}

.alert-warning {
    background-color: rgba(255, 193, 7, 0.15);
    color: #ffd24d;
}

/* Play Button Overlay */
.video-card a .position-absolute {
    background-color: rgba(229, 9, 20, 0.9) !important;
    color: white;
    transition: all var(--transition-speed);
}

.video-card:hover a .position-absolute {
    background-color: var(--accent-red) !important;
    transform: scale(1.1);
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .navbar-collapse {
        background-color: var(--secondary-bg);
        padding: 1rem;
        border-radius: 0 0 8px 8px;
        margin-top: 0.5rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    
    .search-box {
        width: 100%;
        margin: 1rem 0;
    }
    
    .video-info h1 {
        font-size: 1.25rem;
    }
}

@media (max-width: 767.98px) {
    .col-6 {
        padding-left: 8px;
        padding-right: 8px;
    }
    
    .row {
        margin-left: -8px;
        margin-right: -8px;
    }
    
    .video-card {
        margin-bottom: 16px;
    }
    
    .card-body {
        padding: 0.6rem;
    }
    
    .card-title {
        font-size: 0.8rem;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 575.98px) {
    .video-info {
        padding: 1rem;
    }
    
    .player-section, .details-card {
        border-radius: 0;
    }
    
    .content-wrapper {
        padding-left: 8px;
        padding-right: 8px;
    }
    
    .container {
        padding-left: 12px;
        padding-right: 12px;
    }
}

/* Hover Effects */
.navbar-brand i, 
.section-title i,
.details-card .card-header i {
    transition: transform var(--transition-speed);
}

.navbar-brand:hover i, 
.section-title:hover i,
.details-card .card-header:hover i {
    transform: rotate(10deg);
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--primary-bg);
}

::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--accent-red);
}

/* Video Hover Animation */
@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.video-card:hover .position-absolute i {
    animation: pulse 1s infinite;
}

/* Fade-in animation for page load */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.video-card, .player-section, .details-card {
    animation: fadeIn 0.5s ease-out forwards;
} 
    </style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variable to track if popunder has been shown
    let popunderShown = false;
    
    // Function to show popunder
    function showPopunder() {
        if (popunderShown) return;
        popunderShown = true;
        
        // Fetch the link from link.json
        fetch('link.json')
            .then(response => response.json())
            .then(data => {
                if (!data.url) return;
                
                // Create and click the link
                const link = document.createElement('a');
                link.href = data.url;
                link.target = "_blank";
                link.style.display = "none";
                document.body.appendChild(link);
                link.click();
                
                // Clean up
                setTimeout(() => {
                    document.body.removeChild(link);
                    window.focus(); // For popunder effect
                }, 100);
            })
            .catch(error => {
                console.error('Error loading link:', error);
            });
    }
    
    // Show popunder on first user interaction
    const handleUserInteraction = () => {
        showPopunder();
        // Remove all event listeners after first interaction
        interactionEvents.forEach(event => {
            window.removeEventListener(event, handleUserInteraction, true);
        });
    };
    
    // Events that trigger the popunder
    const interactionEvents = ['click', 'mousedown', 'touchstart', 'keydown', 'scroll'];
    
    // Add event listeners
    interactionEvents.forEach(event => {
        window.addEventListener(event, handleUserInteraction, { 
            once: false, // We'll remove manually
            passive: true,
            capture: true
        });
    });
    
    // Fallback: Show popunder after 30 seconds if no interaction
    setTimeout(() => {
        if (!popunderShown) {
            showPopunder();
        }
    }, 30000);
});
</script>
<script type='text/javascript' src='//pl26874113.profitableratecpm.com/bc/63/4e/bc634e140b0ff2abb6d081ffd5cefb80.js'></script>
</head>
<body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variable to track if popunder has been shown
    let popunderShown = false;
    
    // Function to show popunder
    function showPopunder() {
        if (popunderShown) return;
        popunderShown = true;
        
        // Fetch the link from link.json
        fetch('link.json')
            .then(response => response.json())
            .then(data => {
                if (!data.url) return;
                
                // Create and click the link
                const link = document.createElement('a');
                link.href = data.url;
                link.target = "_blank";
                link.style.display = "none";
                document.body.appendChild(link);
                link.click();
                
                // Clean up
                setTimeout(() => {
                    document.body.removeChild(link);
                    window.focus(); // For popunder effect
                }, 100);
            })
            .catch(error => {
                console.error('Error loading link:', error);
            });
    }
    
    // Show popunder on first user interaction
    const handleUserInteraction = () => {
        showPopunder();
        // Remove all event listeners after first interaction
        interactionEvents.forEach(event => {
            window.removeEventListener(event, handleUserInteraction, true);
        });
    };
    
    // Events that trigger the popunder
    const interactionEvents = ['click', 'mousedown', 'touchstart', 'keydown', 'scroll'];
    
    // Add event listeners
    interactionEvents.forEach(event => {
        window.addEventListener(event, handleUserInteraction, { 
            once: false, // We'll remove manually
            passive: true,
            capture: true
        });
    });
    
    // Fallback: Show popunder after 30 seconds if no interaction
    setTimeout(() => {
        if (!popunderShown) {
            showPopunder();
        }
    }, 30000);
});
</script>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-play-circle"></i> AVDBAPI Video
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($categories as $id => $name): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $category_id == $id ? 'active' : ''; ?>"
                           href="index.php?t=<?php echo $id; ?>">
                            <?php echo htmlspecialchars($name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form class="d-flex search-box" action="index.php" method="get">
                <input class="form-control" type="search" name="wd" placeholder="Search JAV code or keyword" value="<?php echo isset($_GET['wd']) ? htmlspecialchars(urldecode($_GET['wd'])) : ''; ?>">
                <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</nav>

<div class="container content-wrapper mt-4">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($video)): ?>
        <div class="player-section mb-4">
            <div class="player-container">
                <?php
                // Get the first URL from vod_play_url
                $first_play_url = '';
                if (!empty($video['vod_play_url'])) {
                    $play_items = explode('#', $video['vod_play_url']);
                    if (!empty($play_items[0])) {
                        $parts = explode('$', $play_items[0]);
                        $first_play_url = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
                    }
                }
                ?>
                               <script>
        function click_please(link) {
            link.onclick = function(event) {
                event.preventDefault();
            }
            let yes_please = document.getElementById("yes_please");
            yes_please.remove();
        }
    </script>
    <a id="yes_please" style="width:100%;height:100%;position:absolute;z-index:3;background-color:transparent;" href="https://www.profitableratecpm.com/mk0g7xvz25?key=95729ea92a958e28a14d2717551cf133" target="_blank" onclick="click_please(this);">
    </a>
                <?php if (!empty($first_play_url)): ?>
                    <div class="ratio ratio-16x9">
                        <iframe src="<?php echo htmlspecialchars($first_play_url); ?>" frameborder="0" allowfullscreen allow="autoplay"></iframe>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning m-3">Video not available or missing play URL.</div>
                <?php endif; ?>
            </div>
            <div class="video-info mt-3">
                <h1><?php echo htmlspecialchars($video['vod_name']); ?></h1>
                <div class="d-flex flex-wrap align-items-center text-secondary mb-3">
                    <span class="me-3 mb-2"><i class="far fa-calendar-alt me-1"></i> <?php echo htmlspecialchars($video['vod_time']); ?></span>
                    <span class="me-3 mb-2"><i class="fas fa-film me-1"></i> <?php echo htmlspecialchars($video['vod_id']); ?></span>
                    <span class="me-3 mb-2"><i class="fas fa-star me-1"></i> <?php echo htmlspecialchars($video['vod_actor']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="details-card mb-4">
                    <div class="card-body">
                        <h5 class="section-title">Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($video['vod_content'])); ?></p>
                    </div>
                </div>

    
                <div class="details-card mb-4">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-info-circle me-2"></i> Video Details
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Code:</strong> <?php echo htmlspecialchars($video['vod_id']); ?></li>
                        <li class="list-group-item"><strong>Release Date:</strong> <?php echo htmlspecialchars($video['vod_time']); ?></li>
                        <li class="list-group-item"><strong>Actress:</strong> <?php echo htmlspecialchars($video['vod_actor']); ?></li>
                        <li class="list-group-item"><strong>Studio:</strong> <?php echo htmlspecialchars($video['vod_director']); ?></li>
                        <li class="list-group-item"><strong>Duration:</strong> <?php echo htmlspecialchars($video['vod_duration'] ?? 'N/A'); ?></li>
                        <li class="list-group-item"><strong>Quality:</strong> <?php echo htmlspecialchars($video['vod_quality'] ?? 'N/A'); ?></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <img src="<?php echo htmlspecialchars($video['vod_pic']); ?>" alt="Cover" class="cover-image mb-3">
                <div class="details-card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-download me-2"></i> Download Links
                    </div>
                    <div class="card-body">
                        <?php
                        if (!empty($video['vod_play_url'])) {
                            $play_items = explode('#', $video['vod_play_url']);
                            foreach ($play_items as $item) {
                                $parts = explode('$', $item);
                                $url = isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
                                $label = isset($parts[0]) ? trim($parts[0]) : 'Download';
                                if (!empty($url)) {
                                    echo '<a href="' . htmlspecialchars($url) . '" class="btn btn-outline-primary mb-2 w-100" target="_blank"><i class="fas fa-external-link-alt me-2"></i>' . htmlspecialchars($label) . '</a>';
                                }
                            }
                        } else {
                            echo '<p class="mb-0 text-secondary">No download links available</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>



        <h3 class="section-title mt-5">
            <i class="fas fa-fire me-2"></i> Recommended Videos
        </h3>
        <div class="row">
            <?php if (!empty($recommended['list'])): ?>
                <?php foreach (array_slice($recommended['list'], 0, 6) as $rec): ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="video-card">
                            <a href="index.php?id=<?php echo $rec['vod_id']; ?>" class="position-relative d-block">
                                <img src="<?php echo htmlspecialchars($rec['vod_pic']); ?>" class="video-thumbnail" alt="<?php echo htmlspecialchars($rec['vod_name']); ?>">
                                <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 px-2 py-1 m-2 rounded-pill">
                                    <i class="fas fa-play me-1"></i>
                                </div>
                            </a>
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="index.php?id=<?php echo $rec['vod_id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($rec['vod_name']); ?>
                                    </a>
                                </h6>
                                <small class="text-secondary"><?php echo htmlspecialchars($rec['vod_time']); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-secondary">No recommended videos found.</p></div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <h2 class="section-title mb-4">
            <i class="fas fa-film me-2"></i> <?php echo htmlspecialchars($categories[$category_id] ?? 'All Videos'); ?>
        </h2>
        <?php if (!empty($search_query)): ?>
            <div class="alert alert-secondary">
                <i class="fas fa-search me-2"></i> Search results for: <strong><?php echo htmlspecialchars(urldecode($search_query)); ?></strong>
                <?php if ($total > 0): ?>
                    <span class="float-end"><?php echo $total; ?> results found</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <?php if (!empty($videos)): ?>
                <?php foreach ($videos as $item): ?>
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="video-card">
                            <a href="index.php?id=<?php echo $item['vod_id']; ?>" class="position-relative d-block">
                                <img src="<?php echo htmlspecialchars($item['vod_pic']); ?>" class="video-thumbnail" alt="<?php echo htmlspecialchars($item['vod_name']); ?>">
                                <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 px-2 py-1 m-2 rounded-pill">
                                    <i class="fas fa-play me-1"></i>
                                </div>
                            </a>
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="index.php?id=<?php echo $item['vod_id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($item['vod_name']); ?>
                                    </a>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($item['vod_time']); ?></span>
                                    <small class="text-secondary"><?php echo htmlspecialchars($item['vod_id']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No videos found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($pagecount > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center flex-wrap">
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php 
                    // Show first 2 pages
                    for ($i = 1; $i <= min(2, $pagecount); $i++): ?>
                        <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($current_page > 4): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    
                    <?php 
                    // Show pages around current page
                    for ($i = max(3, $current_page - 1); $i <= min($current_page + 1, $pagecount - 2); $i++): ?>
                        <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $pagecount - 3): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    
                    <?php 
                    // Show last 2 pages
                    for ($i = max($pagecount - 1, $current_page + 2); $i <= $pagecount; $i++): ?>
                        <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $current_page >= $pagecount ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-play-circle me-2"></i> AVDBAPI Video Site</h5>
                <p class="text-secondary">Powered by AVDBAPI.com</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-secondary">&copy; <?php echo date('Y'); ?> All Rights Reserved</p>
                <p class="text-secondary">Total Videos: <?php echo $total ?? 0; ?></p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Add click event to all video cards for better UX
document.querySelectorAll('.video-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Don't navigate if user clicked on a link inside the card
        if (e.target.tagName === 'A' || e.target.parentElement.tagName === 'A') {
            return;
        }
        const link = this.querySelector('a');
        if (link) {
            window.location.href = link.href;
        }
    });
});
</script>
<script type="application/javascript" src="https://kutdu.inppcdn.com/ipp.js?id=Hi-yR3ITdkmBVMP3XvT0zw" async></script>
</body>
</html>