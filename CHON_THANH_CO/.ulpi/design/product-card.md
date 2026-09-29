# Product Card Redesign

## Design read

The current card is clean but reads as a static image tile. The redesign makes
the engineering value visible before the user opens the detail page.

## User goal

A project engineer should identify product family, key strength, product code,
and next action in under three seconds.

## Component contract

Component: `resources/js/components/ProductCard.vue`

Preserve:

- existing `product`, `variant`, `showCategory`, and `showStrength` props;
- router navigation to `/products/:slug`;
- `LazyImage` and existing fallback image;
- mobile horizontal layout.

Add:

- `featured` variant: visual discovery, lighter metadata.
- `catalog` variant: stronger spec rail and quote-oriented action.
- use available `product.strength_label`, `product.code`, and category name;
  gracefully omit unavailable values.

## Desktop layout

1. Image area, aspect ratio 4:3, with subtle scale on hover.
2. Category pill at top-left.
3. Optional `strength_label` as a navy technical badge at bottom-left.
4. Rotating square arrow mark at top-right on hover.
5. Content area with product name at 19px minimum.
6. **Spec rail:** bordered-top strip containing:
   - left: product code in uppercase mono-like tracking;
   - right: `THÔNG SỐ` or strength label when available.
7. Footer CTA: `Xem chi tiết` with arrow; catalog variant may show
   `Nhận báo giá` wording but must remain one router link.

## Mobile layout

- Keep a compact horizontal card.
- Image minimum 80px square.
- Category at 11px minimum.
- Product name at 14px minimum.
- Add one concise spec line below title when strength is available.
- Keep arrow in a dedicated right-aligned area.

## States

- Missing image: existing fallback.
- Missing category: `Geosynthetics`.
- Missing code/strength: collapse the corresponding spec item without leaving
  empty spacing.
- Long names: clamp to two lines and preserve equal card rhythm.
- Keyboard focus: clear primary outline and no hover-only information.

## Acceptance criteria

- Card is visibly richer without adding unrelated controls.
- Technical value is readable without entering detail.
- Desktop catalog grid has equal-height cards.
- Mobile remains compact and touch-friendly.
- No text below 11px.
- `npm run build` succeeds.

## Build handoff

Implement exactly this spec using the locked language in `.ulpi/design/DESIGN.md`.
Do not redesign unrelated components or introduce a new design system.
