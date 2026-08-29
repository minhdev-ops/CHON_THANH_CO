# DESIGN.md — CHON THANH Design System (Brown Earth Tone Edition)

## Brand Identity
- **Brand Name**: CHON THANH Geosynthetics
- **Industry**: Civil engineering, geosynthetics, infrastructure
- **Target**: B2B decision-makers, government contractors, site engineers
- **Style**: Corporate / Modern / Warm Professional with goldensea.net.vn inspiration

## Color Palette — Brown Earth Tones

### Primary Colors (Rich Brown)
| Token | Hex | Usage |
|-------|-----|-------|
| `primary` | `#5D4037` | Headers, nav, footer, text headings |
| `primary-dark` | `#3E2723` | Dark variant, footer bg, hero overlays |
| `primary-light` | `#8D6E63` | Light accents, hover states |
| `primary-container` | `#5D4037` | Secondary containers, banners |
| `on-primary` | `#ffffff` | Text on primary surfaces |

### Brand Accent (Deep Orange)
| Token | Hex | Usage |
|-------|-----|-------|
| `brand-orange` | `#D84315` | Primary CTA buttons, key actions, links |
| `tertiary` | `#D84315` | Same as brand-orange for consistency |
| `tertiary-container` | `#FF7043` | Accent text, hover states |

### Secondary (Eco Green)
| Token | Hex | Usage |
|-------|-----|-------|
| `secondary` | `#2E7D32` | Eco indicators, sustainability badges |
| `secondary-container` | `#C8E6C9` | Tags, badges |

### Surface / Neutral (Warm Tones)
| Token | Hex | Usage |
|-------|-----|-------|
| `background` | `#FFFBF5` | Main background (warm white) |
| `surface` | `#FFFBF5` | Main background |
| `surface-container-low` | `#FFF8F0` | Subtle section backgrounds |
| `surface-container` | `#F5EDE4` | Alternating sections (warm beige) |
| `surface-container-high` | `#EDE4D9` | Header backgrounds |
| `surface-container-lowest` | `#ffffff` | Card backgrounds |
| `on-surface` | `#3E2723` | Primary text (dark brown) |
| `on-surface-variant` | `#5D4037` | Secondary text (medium brown) |
| `outline` | `#BCAAA4` | Borders (brown 200) |
| `outline-variant` | `#D7CCC8` | Subtle borders (brown 100) |

## Typography
- **Heading Font**: Playfair Display (serif) — elegant, corporate
- **Body Font**: DM Sans (sans-serif) — clean, modern
- **Scale**:
  - Display LG: 48px / 56px / -0.02em / 700
  - Display LG Mobile: 32px / 40px / -0.01em / 700
  - Headline MD: 24px / 32px / -0.01em / 600
  - Body LG: 18px / 28px / 400
  - Body MD: 16px / 24px / 400
  - Label Bold: 14px / 20px / 0.05em / 600 (uppercase)
  - Caption: 12px / 16px / 400

## Layout
- **Container**: 1280px max-width centered
- **Grid**: 12-column (desktop), 1-column (mobile)
- **Base unit**: 8px
- **Margins**: 64px desktop, 16px mobile
- **Section padding**: 64px (py-16 to py-20)
- **Gutter**: 24px
- **Border Radius**: 2xl (1rem) for cards, full for buttons

## Design Principles
- **Warmth**: Brown earth tones create a professional yet approachable feel
- **Clarity**: Clean typography hierarchy with serif headings
- **Motion**: Smooth 300-500ms transitions with cubic-bezier easing
- **Depth**: Layered shadows and subtle hover effects
- **Consistency**: Unified color palette across all pages

## Components

### Buttons
- Primary: `bg-brand-orange` + white text + rounded-full + shadow-md
- Secondary: transparent + `border-2 border-primary` + rounded-full
- Hover: `bg-primary-dark` with shadow-lg + group-hover:translate-x-1

### Cards
- Product/Project cards: `bg-surface-container-lowest` + `border-outline-variant/50` + `rounded-2xl`
- Hover: `hover:shadow-xl` + `hover:-translate-y-1` + `hover:border-brand-orange/30`
- Image zoom: `group-hover:scale-105` with 700ms ease-out

### Forms & Inputs
- All inputs use `focus:border-brand-orange` + `focus:ring-brand-orange` for accent pop
- Rounded-xl for modern feel
- Subtle background with `bg-surface-container-low`

### Navigation & Layout (Header)
- **Background**: Solid primary brown (`#B89B88`) for the entire header to ensure brand identity.
- **Center Menu Container (Pill)**: Large, pill-shaped (`rounded-[12px]`) container with white/light-grey background (`#F7F3F0`) to create strong contrast against the brown header.
- **Text & Typography**: Large and bold fonts. Menu text is dark brown (`#4A403B`) inside the white pill. Logo text is white against the brown background.
- **Hotline CTA**: High-prominence white pill container (`bg-white rounded-full p-2 pr-6 shadow-xl`) enclosing a brown phone icon and large, bold dark text (`22px Black`). Features a hover lift effect (`hover:-translate-y-1`) and ping animation ring.
- **Dropdowns**: White bg + `rounded-[10px]` + `shadow-xl` + hover effects using primary brown.

### Footer
- Dark brown bg (`bg-primary-dark`) with decorative blur elements
- 4-column grid: Brand, Quick Links, Services, Contact
- Social icons with hover: `hover:bg-brand-orange/20`

### Section Headers
- Centered layout with `section-divider` (60px gradient line)
- Serif font for headings
- Orange uppercase label above headings

## Animations
- Page transitions: 0.4s fade + translateY (16px enter, 8px leave)
- Scroll reveal: 0.7s cubic-bezier fade + translateY(30px)
- Card hover: -6px translateY + enhanced shadow (350ms)
- Image zoom: scale(1.08) with 700ms ease-out
- Hero Banner: Ken Burns effect for cinematic feel
- Hotline: Ring + bounce + nudge animations
- Custom scrollbar: 8px thin, brown tones
- Text selection: Brown primary bg + white text

## Anti-Patterns to Avoid
- ❌ No purple gradients or blue primary colors
- ❌ No generic Roboto/Inter fonts (use Playfair Display + DM Sans)
- ❌ No sharp corners (use rounded-2xl)
- ❌ No harsh shadows (use warm-toned layered shadows)
- ❌ No cold gray surfaces (always use warm beige tones)
