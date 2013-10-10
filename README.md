# PHP Sprites

A simple PHP script that scans a folder of images and spits out a single spritesheet PNG and associated CSS file.

## Usage

Include it like a regular stylesheet:

```html
<link href="spritesheet.php?dir=images/&class=sprite" rel="stylesheet" type="text/css" />
```

Or import from within your CSS like this:

```css
@import url("spritesheet.php?dir=images/&class=sprite");
```

The only two options are passed along as query string variables:

- `dir` is the path to the folder with the images in it (relative to the PHP file and __don't forget the trailing slash__)
- `class` is the "base" CSS class (i.e. "sprite", "ui-sprite")

To reference the sprites in your HTML you'd do it like this for an image named `some-image.png`:

```html
<div class="sprite some-image"></div>
```

## Retina support

If you put an image with an @2x suffix on the filename (for exmaple `some-image@2x.png`) it will be added to the spritesheet and automatically served to high-DPI devices. Check out the demo to see it in action!

## Authors

[Steve Palmer](https://github.com/spalmer)
