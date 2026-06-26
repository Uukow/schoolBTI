# App Icon Size Guide

## Recommended Icon Specifications

### Source Image (`logo-icon.png`)
- **Recommended Size:** 1024x1024 pixels
- **Format:** PNG with transparency
- **Background:** Transparent or solid color
- **Content Area:** The actual icon content should be within a "safe area" of approximately 768x768 pixels (75% of the canvas) to ensure proper padding on all devices

### Why 1024x1024?
This is the standard size used by `flutter_launcher_icons` to generate all required icon sizes for:
- **Android:** mdpi (48px), hdpi (72px), xhdpi (96px), xxhdpi (144px), xxxhdpi (192px)
- **iOS:** All sizes from 20x20 to 1024x1024
- **Adaptive Icons:** Foreground layer for Android 8.0+

### Safe Area Guidelines
When designing your icon:
- Keep important content within the center 75% of the canvas (768x768px area)
- Leave 128px padding on all sides
- This ensures the icon looks good on devices with rounded corners and different icon shapes
- **If your icon appears too large, reduce the icon content size to fit within the safe area**

### Current Icon Status
Your current `logo-icon.png` file is being used to generate icons. If the icon appears too large on devices:

1. **Check the source image size:**
   - Open `assets/images/logo-icon.png` in an image editor
   - Verify it's 1024x1024 pixels
   - If larger, resize it to 1024x1024px

2. **Adjust the icon content:**
   - Ensure the icon graphic is centered
   - Add padding around the edges (use the safe area)
   - The icon should not fill the entire canvas
   - **Reduce the icon graphic size to 70-75% of the canvas** to prevent it from appearing too large

3. **Regenerate icons:**
   ```bash
   flutter pub run flutter_launcher_icons
   ```

### Splash Screen Image (`logo-dark.png`)
- **Recommended Size:** 1024x1024 pixels or larger (will be scaled down)
- **Format:** PNG
- **Background:** Can be transparent or include background
- **Content:** Logo should be centered and appropriately sized

### Checking Your Current Icon Size
To check the dimensions of your current icon file:
1. Open the file in any image viewer/editor
2. Check the file properties
3. Use image editing software to verify dimensions

### Fixing an Icon That's Too Large
If your icon appears too large on devices:

**Option 1: Resize the icon content (Recommended)**
- Open `logo-icon.png` in an image editor (Photoshop, GIMP, Canva, etc.)
- Create a new 1024x1024px canvas
- Place your icon graphic in the center
- Scale the icon graphic to approximately 70-75% of the canvas (about 700-750px)
- Add padding/whitespace around the icon
- Save and regenerate icons

**Option 2: Adjust in flutter_launcher_icons config**
- The package automatically scales from your source image
- Ensure your source image has proper padding
- The icon content should not fill the entire canvas

### Generated Icon Sizes
After running `flutter pub run flutter_launcher_icons`, the following sizes are generated:

**Android:**
- mdpi: 48x48px
- hdpi: 72x72px
- xhdpi: 96x96px
- xxhdpi: 144x144px
- xxxhdpi: 192x192px
- Adaptive icon foreground: 108x108px (safe area)

**iOS:**
- 20x20px (@1x, @2x, @3x)
- 29x29px (@1x, @2x, @3x)
- 40x40px (@1x, @2x, @3x)
- 60x60px (@2x, @3x)
- 76x76px (@1x, @2x)
- 83.5x83.5px (@2x)
- 1024x1024px (App Store)
