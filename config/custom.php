<?php

// Custom Config
// -------------------------------------------------------------------------------------
return [
  'custom' => [
    'myLayout' => 'vertical', // Options[String]: vertical(default), horizontal
    'myTheme' => 'theme-default', // Options[String]: theme-default(default), theme-bordered, theme-semi-dark
    'myStyle' => 'dark', // Options[String]: light(default), dark & system mode
    'myRTLSupport' => true, // options[Boolean]: true(default), false // To provide RTLSupport or not
    'myRTLMode' => false, // options[Boolean]: false(default), true // To set layout to RTL layout  (myRTLSupport must be true for rtl mode)
    'hasCustomizer' => true, // options[Boolean]: true(default), false // Display customizer or not THIS WILL REMOVE INCLUDED JS FILE. SO LOCAL STORAGE WON'T WORK
    'displayCustomizer' => true, // options[Boolean]: true(default), false // Display customizer UI or not, THIS WON'T REMOVE INCLUDED JS FILE. SO LOCAL STORAGE WILL WORK
    'contentLayout' => 'wide', // options[String]: 'compact', 'wide' (compact=container-xxl, wide=container-fluid)
    'navbarType' => 'sticky', // options[String]: 'sticky', 'static', 'hidden' (Only for vertical Layout)
    'footerFixed' => true, // options[Boolean]: false(default), true // Footer Fixed
    'menuFixed' => true, // options[Boolean]: true(default), false // Layout(menu) Fixed (Only for vertical Layout)
    'menuCollapsed' => false, // options[Boolean]: false(default), true // Show menu collapsed, (Only for vertical Layout)
    'headerType' => 'fixed', // options[String]: 'static', 'fixed' (for horizontal layout only)
    'showDropdownOnHover' => true, // true, false (for horizontal layout only)
    'customizerControls' => [
      'rtl',
      'style',
      'headerType',
      'contentLayout',
      'layoutCollapsed',
      'layoutNavbarOptions',
      'themes',
    ], // To show/hide customizer options 
  ],
];
