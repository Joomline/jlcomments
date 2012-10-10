/*
---
description: demo application 

license: MIT-style

authors: 
-akaIDIOT
-Eva Loesch
-Philip Fieber
-Christian Merz

version: 0.1

requires:
core/1.2.4:
- Class
- Class.Extras 
- Element 
- Element.Event
- Selectors
more/1.2.4:
- Element.Delegation

provides: TabPane
...
*/
document.addEvent('domready', function(){

	var tabPane = new TabPane('demo_small');
    
	$('demo_small').addEvent('click:relay(.remove)', function(e) {
		new Event(e).stop();
		var parent = e.target.getParent('.tab');
		tabPane.closeTab(parent);
	});
});
