---
name: Modern Utility
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#424656'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#737687'
  outline-variant: '#c2c6d9'
  surface-tint: '#0053da'
  primary: '#004cca'
  on-primary: '#ffffff'
  primary-container: '#0062ff'
  on-primary-container: '#f3f3ff'
  inverse-primary: '#b4c5ff'
  secondary: '#565e74'
  on-secondary: '#ffffff'
  secondary-container: '#dae2fd'
  on-secondary-container: '#5c647a'
  tertiary: '#9e3100'
  on-tertiary: '#ffffff'
  tertiary-container: '#c84000'
  on-tertiary-container: '#fff1ed'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#dae2fd'
  secondary-fixed-dim: '#bec6e0'
  on-secondary-fixed: '#131b2e'
  on-secondary-fixed-variant: '#3f465c'
  tertiary-fixed: '#ffdbcf'
  tertiary-fixed-dim: '#ffb59c'
  on-tertiary-fixed: '#390c00'
  on-tertiary-fixed-variant: '#832700'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display:
    fontFamily: Manrope
    fontSize: 48px
    fontWeight: '800'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  h1:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  h2:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '700'
    lineHeight: '1.3'
  h3:
    fontFamily: Manrope
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Manrope
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Manrope
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
  label-sm:
    fontFamily: Manrope
    fontSize: 13px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.03em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1280px
  gutter: 24px
  margin-page: 40px
  card-padding: 24px
---

## Brand & Style

The design system is anchored in a philosophy of **Functional Minimalism**. It prioritizes high-density information delivered through a low-density visual interface. The target audience consists of power users and professionals who value efficiency over decoration. 

The aesthetic is heavily influenced by modern dashboard design: structured, predictable, and clean. By utilizing expansive whitespace and a rigorous grid, the UI recedes into the background, allowing the application content (app icons, metadata, and download actions) to take center stage. The emotional response should be one of "calm productivity" and "technical reliability."

## Colors

This design system utilizes a high-clarity neutral palette to define the architecture, with a singular "Electric Blue" reserved exclusively for primary intent.

- **Primary (#0062FF):** Used for "Download," "Install," and primary navigation states. It is the beacon of action.
- **Secondary (#0F172A):** A deep slate used for high-contrast typography and iconography to ensure legibility.
- **Surface/Neutral (#F8FAFC):** The foundation of the dashboard. Subtle shifts in grey (Slate 50 to Slate 200) are used to differentiate card backgrounds from the main canvas.
- **Success/Accent:** A secondary vibrant cyan is used sparingly for progress bars or "Update Available" badges to maintain visual interest without breaking the minimalist constraint.

## Typography

The typography strategy relies on **Manrope** for its geometric yet approachable character. The system uses a tight scale for headlines to maintain a professional dashboard aesthetic, while body text is given generous line height to promote scanning.

Vertical rhythm is maintained by adhering to a base-4 system. Labels for metadata (file size, version number) should utilize the `label-sm` style with increased letter spacing to distinguish them from interactive body text.

## Layout & Spacing

The design system employs a **Fixed Grid** model for the main content area to ensure a consistent reading experience across large displays. 

- **Grid:** A 12-column grid is used for the main dashboard view. 
- **Card Spacing:** App cards typically span 3 or 4 columns depending on the view density. 
- **Whitespace:** High margins (40px+) are used between major sections (e.g., "Featured" vs "All Apps") to prevent visual clutter.
- **Rhythm:** All padding and margins must be multiples of 8px to ensure a mathematically sound layout.

## Elevation & Depth

To maintain a modern, flat aesthetic, this design system avoids heavy drop shadows. Instead, it uses **Low-Contrast Outlines** and **Tonal Layers** to create depth.

- **Level 0 (Background):** The page body uses a soft neutral tint (#F8FAFC).
- **Level 1 (Cards/Sidebar):** Pure white (#FFFFFF) surfaces with a 1px border (#E2E8F0).
- **Level 2 (Hover/Active):** A very soft, diffused ambient shadow (0px 4px 20px rgba(0,0,0,0.04)) is applied only when a user interacts with a card, signaling lift.
- **Interaction:** Buttons use a subtle inner-light reflection to appear slightly tactile without being skeuomorphic.

## Shapes

The shape language is "Softly Geometric." A standard radius of **0.5rem (8px)** is applied to all primary cards and input fields. This provides a professional but modern feel that avoids the "toy-like" appearance of fully rounded pills while being more inviting than sharp corners.

App icons within the dashboard should follow a consistent "Squircle" mask to maintain a unified platform look, regardless of the original icon's shape.

## Components

### Buttons
- **Primary:** Solid Electric Blue with white text. High-contrast, 48px height for main actions.
- **Secondary:** Transparent background with a 1px slate border. Used for "Details" or "View More."

### Cards (App Items)
The core of the dashboard. Each card contains:
- Top-left: App Icon (64px).
- Top-right: Download Button (Icon only or compact).
- Center: App Name (H3) and Category (Label).
- Bottom: Metadata row (Size, Rating, Compatibility) in `label-sm`.

### Progress Indicators
Download progress is shown via a thin 4px linear bar at the bottom of the card or button, using the vibrant Cyan accent color to indicate active data transfer.

### Search Bar
A full-width or wide-span input field with a subtle search icon. It should use a white background and a 1px border, expanding slightly on focus to provide a clear interactive state.

### Category Chips
Small, rounded-lg badges used for filtering. When inactive, they use a light grey background; when active, they switch to the Primary Blue with white text.