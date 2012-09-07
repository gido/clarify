<?php

/**
 * Clarify.
 * 
 * Copyright (C) 2012 Roger Dudler <roger.dudler@gmail.com>
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

lock();

$project_id = intval($route[2]);

// check project permissions
permission($project_id, 'VIEW');

$project = $db->single("SELECT id, creator, slug, name FROM project WHERE id = '" . $project_id . "'");
if (!$project) { die(); }
$screens = $db->data("SELECT id, title, description, code FROM screen WHERE project = '" . $project['id'] . "' ORDER BY title ASC");
$colors = $db->data("SELECT id, hex, name, name_css, r, g, b, alpha FROM project_color WHERE project = '" . $project['id'] . "' ORDER BY hue ASC", "id");
$comments = $db->data("SELECT d.id, d.content, d.nr, d.layer, d.screen FROM comment d LEFT JOIN screen s ON s.id = d.screen WHERE s.project = '" . $project['id'] . "'");
$fonts = $db->data("SELECT * FROM project_font WHERE project = " . $project_id);
$modules = $db->data("SELECT * FROM project_module WHERE project = " . $project_id);
$layers = array();
foreach ($comments as $comment) {
    $layers[$comment['screen']][] = $comment;
}
?>
<!DOCTYPE html>
<html class="mod modLayout skinLayoutGuide">
<head>
    <title>Styleguide - Clarify</title>
    <?php require 'partials/head.php' ?>
</head>
<body>
    <div class="mod modGuide">
        <div class="mod modToolbar">
            <div class="section section-nav">
                <a class="button btn-screen" href="/project/<?php echo $project['creator']; ?>/<?php echo $project['slug']; ?>/"><i></i><div class="tt">Back</div></a>
            </div>
        </div>
        <h1><?php echo $project['name'] ?></h1>
        <div class="chapter">
            <h2>Screens</h2>
            <?php foreach ($screens as $index => $screen) { ?>
            <h3><?php echo $index + 1 ?>. <?php echo $screen['title'] ?></h3>
            <div class="screen"><script type="text/javascript" src="<?php echo R ?>embed/<?php echo $screen['code'] ?>/580"></script></div>
            <?php if (isset($layers[$screen['id']])) { ?>
            <ul class="definitions">
                <?php foreach ($layers[$screen['id']] as $comment) { ?>
                <li>
                    <div class="dot">
                        <div class="nr"><?php echo $comment['nr'] ?></div>
                    </div>
                    <p><?php echo $comment['content'] ?>&nbsp;</p>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
            <p><?php echo $screen['description'] ?></p>
            <?php } ?>
        </div>

        <?php if (sizeof($modules) > 0) { ?>
        <div class="chapter pagebreak">
            <h2>Modules</h2>
            <div class="modules">
                <?php foreach ($modules as $index => $module) { ?>
                <h3><?php echo $index + 1 ?>. <?php echo $module['name'] ?></h3>
                <div class="module">
                    <div class="screenshot"><img src="<?php echo R .'upload/modules/' . $project_id .'/'. md5($module['id'] . config('security.general.hash')) ?>.png" /></div>
                    <div class="meta"><div class="code">&lt;div class="mod mod-<?php echo slug($module['name']) ?>"&gt;&lt;/div&gt;</div></div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <div class="chapter pagebreak">
            <h2>Colors</h2>
            <h3>Palette</h3>
            <div class="colors">
            <?php foreach ($colors as $color) { ?>
            <div class="color">
                <div class="box" style="background: #<?php echo $color['hex'] ?>;<?php echo $color['hex'] == 'ffffff' ? 'border: 1px solid #666;' : '' ?>"></div>
                <div class="meta meta-hex">#<?php echo strtoupper($color['hex']) ?></div>
                <div class="meta meta-rgb"><?php echo $color['r'] ?>, <?php echo $color['g'] ?>, <?php echo $color['b'] ?></div>
                <div class="meta meta-less">@<?php echo $color['name_css'] ?></div>
                <div class="meta meta-name">
                <?php if ($color['name'] != '') { ?>
                <?php echo $color['name'] ?><br />
                <?php } ?>
                </div>
            </div>
            <?php } ?>
            </div>
        </div>

        <?php if (sizeof($fonts) > 0) { ?>
        <div class="chapter pagebreak">
            <h2>Fonts</h2>
            <?php foreach ($fonts as $font) { ?>
            <?php $color = $colors[$font['color']]; ?>
            <h3><?php echo $font['name'] ?> - .<?php echo $font['name_css'] ?></h3>
            <div class="fonts">
                <div class="font">
                    <div class="preview" style="font-family: <?php echo $font['family'] ?>; font-size: <?php echo $font['size'] ?>px; font-weight: <?php echo $font['weight'] ?>; font-style: <?php echo $font['style'] ?>; color: #<?php echo $color['hex'] ?>; <?php echo $font['decoration'] != null ? 'text-decoration:' . $font['decoration'] .';' : '' ?>">
                    <?php echo $font['name'] ?>, <?php echo $font['family'] ?>, <?php echo $font['size'] ?>px
                    </div>
                </div>
                <div class="font-meta">
                    <div>Line Height: <strong><?php echo $font['line_height'] ?></strong></div>
                    <div>Weight: <strong><?php echo $font['weight'] ?></strong></div>
                    <div>Transform: <strong><?php echo $font['transform'] ?></strong></div>
                </div>
                <!-- NORMAL -->
                <div class="color">
                    <div class="state">Normal</div>
                    <div class="box" style="background: #<?php echo $color['hex'] ?>;<?php echo $color['hex'] == 'ffffff' ? 'border: 1px solid #666;' : '' ?>"></div>
                    <div class="meta meta-hex">#<?php echo strtoupper($color['hex']) ?></div>
                    <div class="meta meta-rgb"><?php echo $color['r'] ?>, <?php echo $color['g'] ?>, <?php echo $color['b'] ?></div>
                    <div class="meta meta-less">@<?php echo $color['name_css'] ?></div>
                    <div class="meta meta-name">
                    <?php if ($color['name'] != '') { ?>
                    <?php echo $color['name'] ?><br />
                    <?php } ?>
                    </div>
                </div>
                <!-- HOVER -->
                <?php if ($colors[$font['color_hover']]) { ?>
                <?php $color = $colors[$font['color_hover']]; ?>
                <div class="color">
                    <div class="state">Hover</div>
                    <div class="box" style="background: #<?php echo $color['hex'] ?>;<?php echo $color['hex'] == 'ffffff' ? 'border: 1px solid #666;' : '' ?>"></div>
                    <div class="meta meta-hex">#<?php echo strtoupper($color['hex']) ?></div>
                    <div class="meta meta-rgb"><?php echo $color['r'] ?>, <?php echo $color['g'] ?>, <?php echo $color['b'] ?></div>
                    <div class="meta meta-less">@<?php echo $color['name_css'] ?></div>
                    <div class="meta meta-name">
                    <?php if ($color['name'] != '') { ?>
                    <?php echo $color['name'] ?><br />
                    <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <!-- ACTIVE -->
                <?php if ($colors[$font['color_active']]) { ?>
                <?php $color = $colors[$font['color_active']]; ?>
                <div class="color">
                    <div class="state">Active</div>
                    <div class="box" style="background: #<?php echo $color['hex'] ?>;<?php echo $color['hex'] == 'ffffff' ? 'border: 1px solid #666;' : '' ?>"></div>
                    <div class="meta meta-hex">#<?php echo strtoupper($color['hex']) ?></div>
                    <div class="meta meta-rgb"><?php echo $color['r'] ?>, <?php echo $color['g'] ?>, <?php echo $color['b'] ?></div>
                    <div class="meta meta-less">@<?php echo $color['name_css'] ?></div>
                    <div class="meta meta-name">
                    <?php if ($color['name'] != '') { ?>
                    <?php echo $color['name'] ?><br />
                    <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <!-- VISITED -->
                <?php if ($colors[$font['color_visited']]) { ?>
                <?php $color = $colors[$font['color_visited']]; ?>
                <div class="color">
                    <div class="state">Visited</div>
                    <div class="box" style="background: #<?php echo $color['hex'] ?>;<?php echo $color['hex'] == 'ffffff' ? 'border: 1px solid #666;' : '' ?>"></div>
                    <div class="meta meta-hex">#<?php echo strtoupper($color['hex']) ?></div>
                    <div class="meta meta-rgb"><?php echo $color['r'] ?>, <?php echo $color['g'] ?>, <?php echo $color['b'] ?></div>
                    <div class="meta meta-less">@<?php echo $color['name_css'] ?></div>
                    <div class="meta meta-name">
                    <?php if ($color['name'] != '') { ?>
                    <?php echo $color['name'] ?><br />
                    <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php require 'partials/foot.php'; ?>
</body>
</html>