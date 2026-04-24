# ACHILLES Design System Reference

## Theme Overview

Your platform uses a **modern, premium black/red gradient theme** with glass-morphism effects, 3D depth, and Tailwind CSS utilities.

---

## 🎨 Color Palette

### Primary Colors
| Use | Color | Hex | CSS Class |
|-----|-------|-----|-----------|
| Gradients | Black → Red | #000000 → #dc2626 | `bg-gradient-to-r from-black to-red-600` |
| Button Default | Bright Red → Dark Red | #ef4444 → #dc2626 | `bg-gradient-to-r from-red-500 to-red-600` |
| Button Hover | Darker Red Gradient | #dc2626 → #991b1b | `hover:from-red-600 hover:to-red-700` |
| Links/Accents | Red | #dc2626 | `text-red-600` |
| Focus Ring | Light Red | rgba(220,38,38,0.1) | `focus:ring-red-500` |

### Status Badge Colors
| Status | Background | Text | Hex |
|--------|------------|------|-----|
| Pending | Yellow | Yellow | `bg-yellow-100 text-yellow-700` |
| Paid | Blue | Blue | `bg-blue-100 text-blue-700` |
| Shipped | Purple | Purple | `bg-purple-100 text-purple-700` |
| Completed | Green | Green | `bg-green-100 text-green-700` |
| Cancelled | Red | Red | `bg-red-100 text-red-700` |

### Neutral Colors
| Element | Color | Hex | CSS Class |
|---------|-------|-----|-----------|
| Headings | Dark Gray | #1e293b | `text-gray-900` |
| Body Text | Slate | #64748b | `text-gray-700` |
| Subtle Text | Light Gray | #9ca3af | `text-gray-600` |
| Backgrounds | Very Light Gray | #f9fafb | `bg-gray-50` |
| Card Backgrounds | White | #ffffff | `bg-white` |
| Borders | Light Gray | #e2e8f0 | `border-gray-200` |

---

## 🏗️ Component Patterns

### 1. Gradient Headers
```html
<h1 class="text-3xl font-bold bg-gradient-to-r from-black to-red-600 bg-clip-text text-transparent">
    Page Title
</h1>
```
- Combines black-to-red gradient with text clipping
- Creates premium, branded look
- Used on all major page headers

### 2. 3D Buttons
```html
<button class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 
                text-white font-semibold py-3 px-6 rounded-lg 
                shadow-md hover:shadow-lg transition">
    Click Me
</button>
```
- Gradient fill with shadow for depth
- Smooth transitions on hover
- Rounded corners (0.5rem-1rem)
- Proper padding for touch-friendly targets

### 3. Card Components (Glass-Morphism)
```html
<div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 
            hover:shadow-lg transition">
    <!-- Content -->
</div>
```
- White background with subtle border
- Rounded corners (0.75rem-1.5rem)
- Soft shadow for depth
- Subtle hover effect (shadow increase)

### 4. Input Fields
```html
<input type="text" 
       class="w-full px-4 py-2 border border-gray-300 rounded-lg 
              focus:ring-2 focus:ring-red-500 focus:border-transparent 
              transition" />
```
- Clean borders with focus ring
- Red focus state for brand consistency
- Proper padding (0.5rem-0.75rem)
- Smooth transitions

### 5. Status Badges
```html
<!-- Pending -->
<span class="inline-block bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-semibold">
    Pending
</span>

<!-- Completed -->
<span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">
    Completed
</span>
```
- Rounded-full (pill shape) for modern look
- Consistent padding and font weight
- Color-coded for quick scanning

### 6. Alert Boxes
```html
<!-- Error Alert -->
<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
    <p class="text-red-700 font-semibold">Error message</p>
</div>

<!-- Success Alert -->
<div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-lg">
    <p class="text-emerald-700 font-semibold">Success message</p>
</div>
```
- Left border accent for visual hierarchy
- Soft background colors
- Proper contrast for readability

### 7. Grid Layouts
```html
<!-- Responsive Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Cards -->
</div>
```
- Mobile-first (1 col)
- Tablet (2 cols at md breakpoint)
- Desktop (3 cols at lg breakpoint)
- Consistent gap (1.5rem)

---

## 📐 Spacing System

### Consistent Values
| Use | Padding | Margin | Class |
|-----|---------|--------|-------|
| Card Padding | 1.5-2rem | - | `p-6 p-8` |
| Input/Button | 0.5-0.75rem | - | `py-2 py-3` |
| Grid Gap | 1.5rem | - | `gap-6` |
| Section Spacing | - | 2rem | `mb-8` |
| Header Spacing | - | 2rem | `mb-8` |
| Group Spacing | 1.5rem | - | `space-y-6` |

---

## 🎭 Typography

### Font Family
```css
font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
```

### Size Hierarchy
| Element | Size | Weight | Class |
|---------|------|--------|-------|
| Page Title | 2rem-2.25rem | 800 (black) | `text-3xl font-bold` |
| Section Title | 1.25rem | 700 (bold) | `text-xl font-bold` |
| Card Title | 1.125rem | 600 (semibold) | `text-lg font-semibold` |
| Body Text | 1rem | 400 (regular) | `text-base` |
| Small Text | 0.875rem | 500 (medium) | `text-sm font-medium` |
| Tiny Text | 0.75rem | 600 (semibold) | `text-xs font-semibold` |

### Font Weight Usage
| Weight | Usage |
|--------|-------|
| 300-400 | Body text, descriptions |
| 500-600 | Labels, button text, small headings |
| 700-800 | Section headings, page titles |

---

## 🔄 Transitions & Effects

### Standard Transitions
```css
transition: all 0.2s ease;
transition-all duration-200;
```

### Hover Effects
- **Shadow**: `hover:shadow-lg`
- **Transform**: `hover:scale-105`, `hover:translate-y-[-4px]`
- **Color**: `hover:bg-red-600`, `hover:text-red-700`

### 3D Depth Effects
- **Box Shadow**: `shadow-sm` to `shadow-xl` for layering
- **Transform**: `translateY(-2px)` on hover
- **Multiple Shadows**: Combine shadows for layered effects

---

## 📱 Responsive Design Breakpoints

| Breakpoint | Width | Usage |
|-----------|-------|-------|
| Default | < 640px | Mobile/Small phones |
| sm | 640px | Tablets |
| md | 768px | Larger tablets |
| lg | 1024px | Laptops |
| xl | 1280px | Large screens |
| 2xl | 1536px | Extra large |

### Common Patterns
```html
<!-- Text Sizing -->
<h1 class="text-2xl sm:text-3xl md:text-4xl">Title</h1>

<!-- Grid Columns -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">

<!-- Spacing -->
<div class="px-4 sm:px-6 lg:px-8">

<!-- Display -->
<div class="block md:hidden">Mobile only</div>
<div class="hidden md:block">Desktop only</div>
```

---

## 🎯 Page Layout Patterns

### Standard Page Layout
```html
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header with Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Page Title</h1>
        </div>
        
        <!-- Content Cards -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <!-- Content -->
        </div>
    </div>
</div>
```

### Two-Column Layout (Desktop)
```html
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Column (2/3 width) -->
    <div class="lg:col-span-2">
        <!-- Primary content -->
    </div>
    
    <!-- Sidebar (1/3 width) -->
    <div>
        <!-- Summary/Actions -->
    </div>
</div>
```

### Admin Dashboard Layout
```html
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Cards -->
</div>

<!-- Main Content -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <!-- Table or List -->
</div>
```

---

## ✨ Premium Design Details

### 1. Loading States
- Use subtle spinner animations
- Disable buttons during submission
- Show loading indicator on forms

### 2. Empty States
- Icon + message + action button
- Friendly, encouraging copy
- Link to relevant content

### 3. Error Handling
- Show validation errors inline
- Use red text (not just red background)
- Keep error messages clear and actionable

### 4. Hover Effects
- Lift cards on hover
- Increase shadow depth
- Color transitions (not instant)

### 5. Focus States
- Always visible focus ring on interactive elements
- Match brand color (red) for consistency
- Proper contrast for accessibility

---

## 🔑 Key Design Principles

1. **Consistency** - Same styles applied across all pages
2. **Hierarchy** - Clear visual priority through sizing and color
3. **Spacing** - Generous whitespace for premium feel
4. **Interactivity** - Smooth transitions and clear feedback
5. **Accessibility** - Proper contrast and focus states
6. **Responsiveness** - Mobile-first approach with desktop enhancements
7. **Brand Alignment** - Black/red gradient theme throughout
8. **Performance** - Minimal animations, optimized shadows

---

## 🎨 Customization Guide

### To Change Primary Color
1. Replace `red-600` with new color
2. Update `gradient-to-r from-black to-[new-color]`
3. Update status badge colors if needed
4. Update focus ring colors

### To Adjust Spacing
1. Find padding values (p-6, p-8)
2. Find margin values (mb-8, space-y-6)
3. Keep consistent across all pages
4. Test on mobile

### To Add New Component
1. Follow existing patterns
2. Use brand colors from palette
3. Maintain spacing consistency
4. Test responsiveness

---

## 📋 Checklist for New Pages

- [ ] Gradient header with brand colors
- [ ] Proper spacing (p-8, mb-8, gap-6)
- [ ] Responsive grid (md:grid-cols-2, lg:grid-cols-3)
- [ ] Focus states on inputs
- [ ] Hover effects on buttons
- [ ] Empty state message
- [ ] Error/success alerts
- [ ] Mobile-responsive layout
- [ ] Consistent typography sizing
- [ ] Brand color usage throughout

---

## 🎬 Complete Design Files

**All View Files with Full Design:**

✅ Admin Pages:
- `admin/users/index.blade.php` - Users list with stats
- `admin/users/create-admin.blade.php` - Create admin form
- `admin/users/edit.blade.php` - Edit user form
- `admin/orders/index.blade.php` - Orders dashboard with stats
- `admin/orders/show.blade.php` - Order details

✅ User Pages:
- `addresses/index.blade.php` - User address list
- `addresses/create.blade.php` - Add new address
- `addresses/edit.blade.php` - Edit address
- `cart/index.blade.php` - Shopping cart
- `checkout/index.blade.php` - Checkout form
- `orders/index.blade.php` - My orders list
- `orders/show.blade.php` - Order details

---

**Last Updated:** April 24, 2026  
**Theme:** Premium Black/Red Gradient  
**Framework:** Tailwind CSS v3 + Inline CSS  
**Status:** Complete & Production Ready ✅
