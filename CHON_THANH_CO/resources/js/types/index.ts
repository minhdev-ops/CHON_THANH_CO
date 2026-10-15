export interface AboutTimeline {
  year: string
  description: string
}

export interface Category {
  slug: string
  name: string
  description?: string
  products_count?: number
}

export interface Application {
  slug: string
  name: string
  description?: string
  products_count?: number
}

export interface Product {
  slug: string
  code: string
  name: string
  image: string
  strength_label?: string | null
  strength_min?: string | number | null
  strength_max?: string | number | null
  category?: Category
  applications?: Application[]
  description?: string
  applications_text?: string | null
  functions?: string | null
  specs?: ProductSpec[]
  images?: ProductImage[]
  documents?: ProductDocument[]
  parent?: ProductVariant | null
  variants?: ProductVariant[]
  strength_grades?: ProductStrengthGrade[]
  related_products?: ProductVariant[]
  projects?: ProjectSummary[]
}

/** Sản phẩm cha: dòng sản phẩm mà các mã cụ thể thuộc về. */
export interface ProductVariant {
  slug: string
  code: string
  name: string
  image: string
}

/** Một cấp cường độ trong bảng cấp của dòng (ART, GET...). */
export interface ProductStrengthGrade {
  grade: string
  unit?: string | null
  note?: string | null
}

export interface ProductDocument {
  id: number
  title: string
  file_url: string
  file_size?: number | null
}

export interface ProductSpec {
  icon?: string
  label: string
  value: string
}

export interface ProductImage {
  image: string
  alt?: string
}

export interface Project {
  slug: string
  name: string
  location: string
  period: string
  area?: string
  hero_image: string
  desc_image?: string
  description?: string
  materials?: ProjectMaterial[]
  gallery?: ProjectImage[]
}

export interface ProjectMaterial {
  name: string
  detail: string
  image: string
}

export interface ProjectImage {
  image: string
  alt?: string
}

/** Công trình dùng vật liệu của dòng sản phẩm đang xem. */
export interface ProjectSummary {
  slug: string
  name: string
  location: string
  hero_image: string
}

export interface Certificate {
  slug: string
  name: string
  description: string
  image: string
  file?: string | null
}

export interface NewsCategory {
  slug: string
  name: string
}

export interface NewsItem {
  slug: string
  title: string
  excerpt: string
  image: string
  published_at: string
  category?: NewsCategory | null
  content?: string
}

export interface FaqItem {
  question: string
  answer: string
}

export interface HomeStat {
  icon: string
  value: string
  label: string
}

export interface WhyChooseUsItem {
  icon: string
  title: string
  description: string
}

export interface Banner {
  section: string
  image?: string | null
  link_to?: string | null
  title?: string | null
  subtitle?: string | null
  text?: string | null
  button_text?: string | null
}

export interface Partner {
  name: string
  image?: string | null
}

export interface HomeData {
  banners: Banner[]
  stats: HomeStat[]
  why_choose_us: WhyChooseUsItem[]
  featured_products: Product[]
  latest_projects: Project[]
}

export interface NavLink {
  label: string
  to: string
}
