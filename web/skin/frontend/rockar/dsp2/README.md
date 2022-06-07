If you want to start working on FE amends please do the following:

1) Open terminal
2) Change directory to the /skin/frontend/rockar/your_theme/

3) Install all the NPM dependencies:
'npm install'

4) Launch compilation process via 'gulp' command

Tutorial on how to add your custom styles:

1) Create appropriate SCSS file in /skin/frontend/rockar/your_theme/assets/scss/components, for example _buttons.scss
2) Add all the necessary styles
3) Open style.scss file located in /skin/frontend/rockar/your_theme/assets/scss/
4) Import your SCSS in appropriate section, for example: @import "components/buttons";
5) Launch 'gulp' to compile your changes

NOTE: All extends should be added in "/skin/frontend/rockar/your_theme/assets/scss/extends" and updated/included with the same way as described above

Tutorial on how to update sprites:

1) Simply add sprite image in "/skin/frontend/rockar/your_theme/assets/sprites" folder.
It will automatically generate sprite in "/skin/frontend/rockar/your_theme/spritesheets/sprites.png" and
will update "/skin/frontend/rockar/your_theme/assets/scss/utils/_sprites.scss" with all the necessary mixins for your sprite

Here is more described workflow:

As a used I added "close-button.png" in sprites folder.
gulp automatically compiles sprites.png image
gulp automatically updates _sprites.scss file with mixin that is the same as your file's name (in our case it is 'close-button')
After all you add it to your class via @include $sprite-close-button

NOTE: If you want to add image as non-sprite, just add it in /skin/frontend/rockar/your_theme/images/ folder


-------------------- Vue.JS Application Development Process --------------------

As you know we are using vue.js framework for our application. Each component should be made using Vue.js, some parts of the component can be created with jQuery.

File Structure:

— /assets/app - root folder of entire vue.js application
	— components/ - vue.js custom components
	— filters/ - vue.js custom filters
	— directives/ - vue.js custom directives
	— utils/ - some kind of helpers that could be written in native JS or jQuery.
	— vendor/ - additional libraries
— /build/ — webpack configuration files
— /js/appvue.js - vue.js compiled application

What is this weird &.vue extension?

Basically it’s “single file component”. We are using webpack and when it’ll compile our application he would split this file in different parts.

<template>tag will hold all the markup for our component, consider everything inside as it would be in a regular *.html file</template>

<script>tag will hold all the logic of our component, consider everything inside as it would be in a regular *.js file</script>

You can go through components and see how it is handled out there or you can check the official documentation in here: http://vuejs.org/guide/application.html

!!! KEEP IN MIND: We are NOT using our styles inside of the *.vue component, we will still use regular SCSS files.

Don't forget that each component you create must be diclared inside the main.js file in the root folder of the application.

Gulp:

Not much changes here, everything works as before, if you use:

'gulp'

command in your terminal you will compile entire project and start the watchers

'gulp compile'

will do the same, but won't start watchers.

Under the hood we will have one more task 'vue-compile' that will be responsible for application campilation process.
Our webpack configured in the way that all javascript will be passed through JSLint and will display any errors you have in the terminal.



