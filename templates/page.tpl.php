<div id="page" class="<?php print $classes; ?>"<?php print $attributes; ?>>

  <header id="header" role="banner" >
  <div class="inside">
  <div class="g-all-row">
  <div class="header-inner">

    <?php if ($page['hgroup_first'] || $page['hgroup_second'] || $page['hgroup_third'] || $logo || $site_name || $site_slogan || $secondary_menu): ?>
    <hgroup class="clearfix">

      <?php if ($page['hgroup_first'] || $logo): ?>
        <div id="hgroup-first">
        <div class="hgroup-inner <?php print $hgroup_first_width ?>">

          <?php if ($logo): ?>
            <div class="logo">
              <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" id="logo">
                <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>"/>
              </a>
            </div>
          <?php endif; ?>

          <?php if ($page['hgroup_first']): ?>
            <?php print render($page['hgroup_first']); ?>
          <?php endif; ?>

        </div> <!-- /.hgroup-first-inner -->
        </div> <!-- /#hgroup-first -->
      <?php endif; ?>

    <?php if ($page['hgroup_second'] || $site_name || $site_slogan): ?>
    <div id="hgroup-second">
    <div class="hgroup-inner <?php print $hgroup_second_width ?>">

      <?php if ($site_name || $site_slogan): ?>
        <div id="name-and-slogan">
          <?php if ($site_name): ?>
            <?php if ($title): ?>
              <div id="site-name">
                <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><?php print $site_name; ?></a>
              </div>
            <?php else: /* Use h1 when the content title is empty */ ?>
              <h1 id="site-name">
                <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home"><?php print $site_name; ?></a>
              </h1>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($site_slogan): ?>
            <div id="site-slogan"><?php print $site_slogan; ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($page['hgroup_second']): ?>
        <?php print render($page['hgroup_second']); ?>
      <?php endif; ?>

    </div> <!-- /.hgroup-second-inner -->
    </div> <!-- /#hgroup-second -->
    <?php endif; ?>

    <?php if ($page['hgroup_third'] || $secondary_menu): ?>
    <div id="hgroup-third" class="<?php print $hgroup_third_classes ?>">
    <div class="hgroup-inner <?php print $hgroup_third_width ?>">

      <?php if ($secondary_menu): ?>
        <nav class="menu">
          <?php print theme('links', array('links' => $secondary_menu, 'attributes' => array('id' => 'secondary', 'class' => array('links', 'clearfix', 'sub-menu')))); ?>
        </nav>
      <?php endif; ?>

      <?php if ($page['hgroup_third']): ?>
        <?php print render($page['hgroup_third']); ?>
      <?php endif; ?>

    </div> <!-- /.hgroup-third-inner -->
    </div> <!-- /#hgroup-third -->
    <?php endif; ?>

    </hgroup>
    <?php endif; ?>

    <?php if ($page['header']): ?>
      <div class="header-region">
      <div class="header-region-inner <?php print $grid_width ?>">
        <?php print render($page['header']); ?>
      </div>
      </div>
    <?php endif; ?>

  </div>
  </div> <!-- /g-all-row -->
  </div> <!-- /inside -->
  </header> <!-- /header -->

  <?php if ($page['navigation'] || $main_menu): ?>
    <nav id="navigation" class="menu<?php if ($breadcrumb) { print ' with-breadcrumb'; } ?>">
      <div class="inside">
      <div class="g-all-row">
      <div class="navigation-inner <?php print $grid_width ?>">
      <?php if ($main_menu): ?>
        <?php print theme('links', array('links' => $main_menu, 'attributes' => array('id' => 'primary', 'class' => array('links', 'clearfix', 'main-menu')))); ?>
      <?php endif; ?>
      <?php if ($page['navigation']): ?>
        <?php print render($page['navigation']); ?>
      <?php endif; ?>
      </div> <!-- /navigation-inner -->
      </div> <!-- /g-all-row -->
      </div> <!-- /inside -->
    </nav>
  <?php endif; ?>


  <div id="main" class="clearfix">
  <div class="inside">
  <div class="g-all-row">

  <article id="content" <?php print $attributes; ?>>
  <div <?php print $content_attributes; ?>>
    <?php if ($breadcrumb || $title|| $messages || $tabs || $action_links): ?>
      <div id="content-header">
        <?php print $breadcrumb; ?>
        <?php if ($page['highlight']): ?>
          <div id="highlight"><?php print render($page['highlight']) ?></div>
        <?php endif; ?>

        <?php if ($title): ?>
          <h1 class="title"><?php print $title; ?></h1>
        <?php endif; ?>

        <?php print $messages; ?>
        <?php print render($page['help']); ?>

        <?php if ($tabs): ?>
          <div class="tabs"><?php print render($tabs); ?></div>
        <?php endif; ?>

        <?php if ($action_links): ?>
          <ul class="action-links"><?php print render($action_links); ?></ul>
        <?php endif; ?>

      </div> <!-- /#content-header -->
    <?php endif; ?>

    <div id="content-area">
      <?php print render($page['content']) ?>
    </div>

    <?php print $feed_icons; ?>
  </div> <!-- /content-inner -->
  </article> <!-- /content -->

  <?php print render($page['sidebar_first']); ?>
  <?php print render($page['sidebar_second']); ?>

  </div> <!-- /g-all-row -->
  </div> <!-- /inside -->
  </div> <!-- /main -->

  <footer id="footer">
  <div class="inside">
  <div class="g-all-row">

    <?php if ($page['footer_first'] || $page['footer_second'] || $page['footer_third'] || $page['footer_fourth']): ?>
    <div id="footer-columns" class="clearfix">
        <?php print render($page['footer_first']); ?>
        <?php print render($page['footer_second']); ?>
        <?php print render($page['footer_third']); ?>
        <?php print render($page['footer_fourth']); ?>
      </div> <!-- /#footer-columns -->
    <?php endif; ?>

    <?php if ($page['footer']): ?>
      <?php print render($page['footer']); ?>
    <?php endif; ?>

  </div> <!-- /g-all-row -->
  </div> <!-- /.inside -->
  </footer>

</div> <!-- /page -->
