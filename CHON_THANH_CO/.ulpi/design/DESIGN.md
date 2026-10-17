# CHƠN THÀNH Design Language

## Identity lock

**Direction:** Industrial Field Manual.

The interface should feel like a premium geosynthetics catalogue used by engineers:
measured, technical, tactile, and confident. It must avoid generic SaaS cards,
soft pastel gradients, excessive glassmorphism, and decorative animation without
information value.

## Visual system

- **Register:** technical brand catalogue.
- **Existing palette:** warm stone primary (`#B89B88`), navy text (`#16243D`),
  canvas neutrals, and dark brown text.
- **Contrast rule:** dark navy for engineering data and text; warm stone only for
  action, emphasis, and interaction state.
- **Shape rule:** medium rounded corners for cards; pills only for taxonomy and
  compact status labels.
- **Spacing rule:** generous image area, compact technical metadata, strong footer
  separation.
- **Type rule:** bold condensed-feeling uppercase labels for metadata; large,
  high-contrast product names; never use text smaller than 11px.

## Signature

Every product card uses a **spec rail**: a compact technical strip between the
product title and the action footer. It contains the strongest available product
value (strength label, code, or category) and visually separates discovery from
the CTA.

## Motion

Motion intensity: 4/10. Use one purposeful hover sequence:
image scales subtly, a navy scrim appears, the corner action mark rotates, and the
spec rail/action text changes to primary. No looping animation.

## Accessibility

- Card remains a single keyboard-focusable router link.
- Visible focus ring uses primary color with offset.
- Text contrast must meet WCAG AA.
- Technical labels remain readable at 11px minimum.
- Decorative overlays are pointer-events-none.
