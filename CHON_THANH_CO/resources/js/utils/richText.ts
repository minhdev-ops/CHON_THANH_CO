/**
 * Markdown nhẹ cho nội dung do admin gõ tay trong ô textarea.
 *
 * Quy tắc hỗ trợ:
 *   "## Tiêu đề"  → heading cấp 2      "### Tiêu đề" → heading cấp 3
 *   "- Mục"       → gạch đầu dòng    "• Mục"       → gạch đầu dòng
 *   "1. Mục"      → danh sách đánh số "1) Mục"      → danh sách đánh số
 *   "**chữ**"    → in đậm
 *   Dòng trống   → ngắt khối
 *
 * Nội dung luôn render bằng text interpolation của Vue, không dùng v-html.
 */

export type RichBlock =
    | { type: 'heading'; level: 2 | 3; text: string }
    | { type: 'paragraph'; text: string }
    | { type: 'list'; ordered: boolean; items: string[] }

/** Một đoạn text nhỏ kèm cờ in đậm, dùng để render bằng <strong>. */
export type InlineSegment = { t: string; b?: boolean }

const HEADING_RE = /^(#{1,3})\s+(.*)$/
const BULLET_RE = /^[-•]\s+(.*)$/
const ORDERED_RE = /^\d+[.)]\s+(.*)$/

/** Tách "**đậm**" thành các segment để render bằng thẻ, không cần v-html. */
export function inlineSegments(text: string): InlineSegment[] {
    const segments: InlineSegment[] = []
    const pattern = /\*\*(.+?)\*\*/g
    let lastIndex = 0
    let match: RegExpExecArray | null

    while ((match = pattern.exec(text)) !== null) {
        if (match.index > lastIndex) {
            segments.push({ t: text.slice(lastIndex, match.index) })
        }
        segments.push({ t: match[1], b: true })
        lastIndex = match.index + match[0].length
    }

    if (lastIndex < text.length) {
        segments.push({ t: text.slice(lastIndex) })
    }

    return segments.length ? segments : [{ t: text }]
}

/** Bỏ hết ký hiệu markdown, trả về text thuần dùng cho tiêu đề hoặc SEO. */
export function stripRichMarkers(text: string): string {
    return text
        .replace(/\*\*(.+?)\*\*/g, '$1')
        .replace(/^(#{1,3})\s+/gm, '')
        .replace(/^[-•]\s+/gm, '')
        .replace(/^\d+[.)]\s+/gm, '')
        .trim()
}

export function parseRichText(text?: string | null): RichBlock[] {
    const blocks: RichBlock[] = []

    for (const raw of (text ?? '').split(/\n+/)) {
        const line = raw.trim()
        if (!line) continue

        const heading = HEADING_RE.exec(line)
        if (heading) {
            blocks.push({
                type: 'heading',
                level: heading[1].length === 3 ? 3 : 2,
                text: heading[2].trim(),
            })
            continue
        }

        const bullet = BULLET_RE.exec(line)
        if (bullet) {
            pushListItem(blocks, bullet[1].trim(), false)
            continue
        }

        const ordered = ORDERED_RE.exec(line)
        if (ordered) {
            pushListItem(blocks, ordered[1].trim(), true)
            continue
        }

        blocks.push({ type: 'paragraph', text: line })
    }

    return blocks
}

/** Các mục liền nhau cùng kiểu gộp vào một list, mục sau khác kiểu thì tách list mới. */
function pushListItem(blocks: RichBlock[], item: string, ordered: boolean): void {
    const last = blocks[blocks.length - 1]
    if (last && last.type === 'list' && last.ordered === ordered) {
        last.items.push(item)
        return
    }
    blocks.push({ type: 'list', ordered, items: [item] })
}