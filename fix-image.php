<?php
// fix-images.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Link database connection architecture
include __DIR__ . '/Config/db.php'; 

echo "<div style='font-family:sans-serif; padding:20px; max-width:600px; margin:0 auto; line-height:1.6;'>";
echo "<h2 style='color:#1e293b;'>Tripistry Image Database Patcher</h2>";

try {
    // 2. Select all existing entries that have missing, empty, or unpatched image properties
    $stmt = $pdo->query("SELECT packID, country FROM package WHERE image_path IS NULL OR image_path = '' OR (image_path NOT LIKE 'http%' AND image_path NOT LIKE '../%')");
    $packages = $stmt->fetchAll();

    if (count($packages) === 0) {
        echo "<p style='color:#16a34a; font-weight:bold;'>✓ All existing vacation packages already have valid active images linked!</p>";
        echo "<a href='login.php' style='color:#2563eb;'>Go to Login Page</a>";
        exit;
    }

    // 3. High-resolution copyright-free travel images categorized by destinations
    $image_library = [
        'south africa' => 'https://images.unsplash.com/photo-1580618672591-eb180b1a973f?q=80&w=800&auto=format&fit=crop',
        'cape town'    => 'https://images.unsplash.com/photo-1580618672591-eb180b1a973f?q=80&w=800&auto=format&fit=crop',
        'france'       => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?q=80&w=800&auto=format&fit=crop',
        'paris'        => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?q=80&w=800&auto=format&fit=crop',
        'japan'        => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=800&auto=format&fit=crop',
        'tokyo'        => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=800&auto=format&fit=crop',
        'bali'         => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?q=80&w=800&auto=format&fit=crop',
        'indonesia'    => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?q=80&w=800&auto=format&fit=crop',
        'maldives'     => 'https://images.unsplash.com/photo-1439066615861-d1af74d74000?q=80&w=800&auto=format&fit=crop',
        'italy'        => 'https://images.unsplash.com/photo-1498503182468-3b51cbb6cb24?q=80&w=800&auto=format&fit=crop',
        'london'       => 'https://images.unsplash.com/photo-1513635269975-59663e0ca1ad?q=80&w=800&auto=format&fit=crop',
        'thailand'     => 'https://images.unsplash.com/photo-1528181304800-2f1702423219?q=80&w=800&auto=format&fit=crop'
    ];

    // Breathtaking default adventure scenery if the country name doesn't match above keywords
    $default_scenery = 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?q=80&w=800&auto=format&fit=crop';

    $updated_counter = 0;
    
    // 4. Update the records
    foreach ($packages as $pkg) {
        $country_clean = strtolower(trim($pkg['country'] ?? ''));
        $assigned_url = $default_scenery;

        // Smart-matching keywords to provide contextual graphics
        foreach ($image_library as $keyword => $url) {
            if (strpos($country_clean, $keyword) !== false) {
                $assigned_url = $url;
                break;
            }
        }

        $updateStmt = $pdo->prepare("UPDATE package SET image_path = :img WHERE packID = :id");
        $updateStmt->execute([
            'img' => $assigned_url,
            'id'  => $pkg['packID']
        ]);
        
        $updated_counter++;
        echo "<p style='margin:4px 0; color:#475569;'>→ Updated Package <strong>#{$pkg['packID']}</strong> ({$pkg['country']})</p>";
    }

    echo "<br><p style='background:#dcfce7; color:#16a34a; padding:12px; border-radius:4px; font-weight:bold;'>🎉 Success! Updated {$updated_counter} old rows with premium travel visuals.</p>";
    echo "<a href='login.php' style='display:inline-block; background:#2563eb; color:white; padding:10px 15px; border-radius:4px; text-decoration:none; font-weight:bold; margin-top:10px;'>Proceed to Live Login View →</a>";

} catch (PDOException $e) {
    echo "<p style='color:#ef4444; font-weight:bold;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>