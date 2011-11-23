
Introduction to Aether

Aether is geared towards a themer that utilizes sass/compass, it also aims to provide theme settings
for tasks better handled on the theme layer. 

This theme is a starter theme, with all the components you should need to build a fixed-width responsive site using re-usable CSS patterns, it supports (out of the box) Handheld Portrait (320px), Handheld Landscape, Tablet Landscape, Tablet Portrait, & Desktop (1140px).

__________________________________________________________________________________________

Installation

- Download Aether from https://github.com/krisbulman/Aether
- Unpack the downloaded file and place the Aether folder in your Drupal installation under 
  one of the following locations:

    * sites/all/themes
    * sites/default/themes
    * sites/example.com/themes 

- Log in as an administrator on your Drupal site and go to 
  Administer > Site building > Themes (admin/build/themes) and make Aether the default theme.

__________________________________________________________________________________________

What are the files for ?
------------------------

- aether.info => provide informations about the theme, like regions, css, settings, js ...
- block.tpl.php => template to edit the blocks
- comment.tpl.php => template to edit the comments
- node.tpl.php => template to edit the nodes (in content)
- page.tpl.php => template to edit the page structure markup
- html.tpl.php => template to the head of the page
- template.php => used to modify drupal's default behavior before outputting HTML through 
  the theme
- theme-settings => used to create additional settings in the theme settings page

In css/
-------

- grid-layout.css => defines the base grid
- screen.css => styles & media queries


In scss/
-------

- base/_utilities.scss => mixins
- base/_variables.scss => should control any set variable in the theme
- layout/_grid-template1.scss => this is a template to build grids using sass @extends (causes css bloat)
- layout/_grid.scss => this is the main code to build responsive grids
- style/_reset.scss => this is a modified normalize.scss, which contains mostly elements
- style/_type.scss => this sets vertical rhythm and font specifications
- style/_patterns.scss => this is a set of re-usable patterns
- style/_forms.scss => for all form elements
- style/_tables.scss => for all table styling
- style/_tabs.scss => for tabs styling
- grid-layout.scss => compiles everything in layout/ and adds any needed compass add-ons
- screen.scss => compiles everything in style/ and adds any needed compass add-ons

__________________________________________________________________________________________

Changing the Layout

The layout used in Aether is grid based, either use layout/_grid-template1.scss or theme-settings (not yet implimented) to define column widths. 

Number of columns per media: 

Handheld: 12 columns
Tablet: 24 columns
Desktop: 32 columns

Classes per media: 
g-<media>-<columns>

How rows are defined: 
.row

This is how the page template is buit in Aether, and it is responsive up from mobile 320px up to desktop 1140px

	1. header
	2. navigation
	3. content
	4. sidebars
	5. footer

__________________________________________________________________________________________


