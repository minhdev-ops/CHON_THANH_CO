/**
 * Dựng link sang trang liên hệ kèm nội dung tư vấn có sẵn.
 *
 * CTA trên toàn site trỏ về /contact?subject=...&body=...&product=...
 * ContactPage đọc các query param này để điền sẵn vào form:
 * - subject  → gợi ý cho ô "Nội dung"
 * - body     → nội dung tư vấn có sẵn (điền thẳng vào textarea)
 * - product  → chọn sẵn sản phẩm trong ô "Sản phẩm quan tâm"
 */

export type ContactSubject =
  | 'quote'
  | 'quote-24h'
  | 'product'
  | 'project'
  | 'certificate'
  | 'capability'
  | 'general'

export interface ContactLinkOptions {
  subject?: ContactSubject
  /** Nội dung tư vấn có sẵn, hiển thị trong ô "Nội dung". */
  body?: string
  /** Tên sản phẩm được chọn sẵn. */
  product?: string
  /** Ghi đè tiền tố nếu muốn tuỳ biến, mặc định lấy từ i18n. */
  greeting?: string
}

const DEFAULT_SUBJECT: ContactSubject = 'general'

/** Tiền tố "Tôi quan tâm đến:" nối vào trước nội dung chi tiết. */
export const contactSubjectText: Record<ContactSubject, string> = {
  quote: 'Tôi quan tâm đến báo giá vật liệu địa kỹ thuật',
  'quote-24h': 'Tôi muốn nhận báo giá trong 24 giờ',
  product: 'Tôi quan tâm đến sản phẩm',
  project: 'Tôi quan tâm đến dự án đã thực hiện',
  certificate: 'Tôi cần xem chứng nhận chất lượng',
  capability: 'Tôi cần xem hồ sơ năng lực',
  general: 'Tôi cần tư vấn',
}

/** Ghép nội dung tư vấn mặc định cho từng chủ đề. */
export const contactBodyText: Record<ContactSubject, string> = {
  quote: 'Tôi đang cần báo giá vật liệu địa kỹ thuật cho dự án của mình.\n\nSản phẩm quan tâm:\nQuy mô dự án:\nLoại công trình:\nThời gian cần hàng:\n\nMong nhận được tư vấn kỹ thuật phù hợp nhất.',
  'quote-24h':
    'Tôi đang cần báo giá vật liệu địa kỹ thuật cho dự án của mình.\n\nSản phẩm quan tâm:\nQuy mô dự án:\nLoại công trình:\nThời gian cần hàng:\n\nMong nhận được tư vấn kỹ thuật phù hợp nhất.',
  product: 'Tôi muốn nhận thông số kỹ thuật và báo giá cho sản phẩm này.\n\nKhối lượng dự kiến:\nMục đích sử dụng:\nThời gian cần hàng:',
  project: 'Tôi muốn tham khảo dự án tương tự để đánh giá năng lực và chất lượng công trình.',
  certificate: 'Tôi cần xin bản chứng nhận chất lượng và giấy uỷ quyền để đưa vào hồ sơ dự thầu.',
  capability: 'Tôi cần xin hồ sơ năng lực của công ty để đưa vào hồ sơ dự thầu.\n\nThông tin dự án:',
  general: 'Tôi cần tư vấn về vật liệu địa kỹ thuật cho dự án của mình.',
}

/**
 * Dựng URL liên hệ kèm nội dung tư vấn có sẵn.
 *
 * @example
 * buildContactLink({ subject: 'quote-24h' })
 * // → '/contact?subject=quote-24h&body=...'
 */
export function buildContactLink(options: ContactLinkOptions = {}): string {
  const subject = options.subject ?? DEFAULT_SUBJECT
  const greeting = options.greeting?.trim()

  const body = (options.body?.trim() || contactBodyText[subject]) as string
  const bodyWithGreeting = greeting ? `${greeting}\n\n${body}` : body

  const params = new URLSearchParams()
  params.set('subject', subject)
  params.set('body', bodyWithGreeting)

  if (options.product?.trim()) {
    params.set('product', options.product.trim())
  }

  return `/contact?${params.toString()}`
}