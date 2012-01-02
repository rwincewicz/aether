// Original code from http://www.blog.highub.com/mobile-2/a-fix-for-iphone-viewport-scale-bug/
// Rewritten version by @mathias, @cheeaun and @jdalton from https://gist.github.com/901295

(function ($) {

Drupal.behaviors.iosbug = {

attach: function(doc) {

var addEvent = 'addEventListener',
type = 'gesturestart',
qsa = 'querySelectorAll',
scales = [1, 1],
meta = qsa in doc ? doc[qsa]('meta[name=viewport]') : [];

function fix() {
meta.content = 'width=device-width,minimum-scale=' + scales[0] + ',maximum-scale=' + scales[1];
doc.removeEventListener(type, fix, true);
}

if ((meta = meta[meta.length - 1]) && addEvent in doc) {
fix();
scales = [.25, 1.6];
doc[addEvent](type, fix, true);
}

}}

})(document);
