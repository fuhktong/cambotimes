<?php
require_once 'config.php';

// Get latest articles
try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name, u.username as author_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN users u ON a.author_id = u.id 
        WHERE a.status = 'published' 
        ORDER BY a.published_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $articles = $stmt->fetchAll();
    
    // Get categories
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $articles = [];
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Latest News</title>
    <meta name="description" content="Stay updated with the latest news from Cambodia and beyond">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1><?php echo SITE_NAME; ?></h1>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#articles">Latest</a></li>
                <?php foreach ($categories as $category): ?>
                    <li><a href="#category-<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                <?php endforeach; ?>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <section id="home">
            <h2>Welcome to <?php echo SITE_NAME; ?></h2>
            <p>Your trusted source for news and information from Cambodia and beyond.</p>
        </section>
        
        <section id="articles">
            <h2>Latest Articles</h2>
            <?php if (empty($articles)): ?>
                <p>No articles available yet. Check back soon!</p>
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <article class="article-card">
                            <?php if ($article['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                            <?php endif; ?>
                            <div class="article-content">
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <p class="article-meta">
                                    By <?php echo htmlspecialchars($article['author_name'] ?? 'Unknown'); ?> | 
                                    <?php echo date('M d, Y', strtotime($article['published_at'])); ?>
                                    <?php if ($article['category_name']): ?>
                                        | <span class="category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="article-excerpt">
                                    <?php echo htmlspecialchars($article['excerpt'] ?? substr(strip_tags($article['content']), 0, 150) . '...'); ?>
                                </p>
                                <a href="article.php?slug=<?php echo urlencode($article['slug']); ?>" class="read-more">Read More</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <section id="about">
            <h2>About <?php echo SITE_NAME; ?></h2>
            <p>We are committed to delivering accurate, timely, and relevant news coverage that matters to our readers. Our team of dedicated journalists works around the clock to bring you the latest developments in politics, business, sports, technology, and more.</p>
        </section>
        
        <section id="contact">
            <h2>Contact Us</h2>
            <p>Have a news tip or want to get in touch? We'd love to hear from you.</p>
            <p>Email: news@cambotimes.com</p>
            <p>Phone: +855 XX XXX XXX</p>
        </section>
    </main>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>