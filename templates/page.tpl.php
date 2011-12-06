<div id="page" class="<?php print $classes; ?>"<?php print $attributes; ?>>

<!-- BEGIN ROW -->
  <header id="header" class="row">

    <?php if ($logo || $site_name || $site_slogan): ?>
    <div id="logo-name-and-slogan">

    <?php if ($logo): ?>
      <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" id="logo">
        <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>"/>
      </a>
    <?php endif; ?>

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

    <?php if ($secondary_menu): ?>
      <nav class="menu">
        <?php print theme('links', array('links' => $secondary_menu, 'attributes' => array('id' => 'secondary', 'class' => array('links', 'clearfix', 'sub-menu')))); ?>
      </nav>
    <?php endif; ?>

      </div>
    <?php endif; ?>

    <?php if ($page['header']): ?>
      <div id="header-region">
        <?php print render($page['header']); ?>
      </div>
    <?php endif; ?>
  </header> <!-- /header -->
<!-- END ROW -->

<!-- BEGIN ROW -->
    <?php if ($main_menu): ?>
      <nav id="navigation" class="menu row <?php if ($breadcrumb) { print ' with-breadcrumb'; } ?>">
        <?php print theme('links', array('links' => $main_menu, 'attributes' => array('id' => 'primary', 'class' => array('links', 'clearfix', 'main-menu')))); ?>
      </nav>
    <?php endif; ?>
<!-- END ROW -->

<!-- BEGIN ROW -->
    <?php if ($breadcrumb): ?>
      <div class="row">
        <?php print $breadcrumb; ?>
      </div>
    <?php endif; ?>
<!-- END ROW -->

<!-- BEGIN ROW -->
  <div id="main" class="clearfix row">

    <?php print render($page['sidebar_first']); ?>

    <article id="content">
        <?php if ($title|| $messages || $tabs || $action_links): ?>
          <div id="content-header">

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

    </article> <!-- /content -->

    <?php print render($page['sidebar_second']); ?>

  </div> <!-- /main -->
<!-- END ROW -->

<!-- BEGIN ROW -->
  <?php if ($page['footer']): ?>
    <div id="footer" class="row">
        <?php print render($page['footer']); ?>
    </div> <!-- /footer -->
  <?php endif; ?>
<!-- END ROW -->

</div> <!-- /page -->
