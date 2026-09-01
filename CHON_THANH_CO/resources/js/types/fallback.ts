import type {
  Product,
  Project,
  NewsItem,
  FaqItem,
  Certificate,
  Category,
  Application,
  HomeStat,
  WhyChooseUsItem,
  AboutTimeline,
} from './index'
import { getYearsOfExperience } from '../utils/experience'

// ============ CATEGORIES ============
export const fallbackCategories: Category[] = [
  { slug: 'vai-kt-khong-det', name: 'Vải địa kỹ thuật không dệt', description: 'Vải địa kỹ thuật không dệt từ sợi PP/PET xuyên kim, dùng cho phân cách, lọc và thoát nước.', products_count: 15 },
  { slug: 'vai-kt-det', name: 'Vải địa kỹ thuật dệt', description: 'Vải dệt cường độ cao cho gia cố nền, ổn định mái dốc và tường chắn.', products_count: 12 },
  { slug: 'luoi-dia-ky-thuat', name: 'Lưới địa kỹ thuật (Geogrid)', description: 'Lưới địa kỹ thuật gia cường nền đường, tường chắn và nền đất yếu.', products_count: 1 },
  { slug: 'tham-3d', name: 'Thảm 3D chống xói mòn', description: 'Thảm 3D kiểm soát xói mòn mái dốc, bờ sông và kênh mương.', products_count: 1 },
  { slug: 'ro-da', name: 'Rọ đá (Gabion)', description: 'Rọ đá gia cố mái dốc, bờ kè và chống xói mòn.', products_count: 1 },
  { slug: 'bang-tham', name: 'Băng thấm (GCL)', description: 'Băng thấm bentonite chống thấm cho hồ chứa, bãi rác và kênh mương.', products_count: 1 },
  { slug: 'mang-chong-tham', name: 'Màng chống thấm HDPE', description: 'Màng HDPE chống thấm tuyệt đối cho hồ chứa, bãi chôn lấp và công trình môi trường.', products_count: 1 },
  { slug: 'luoi-thep-day-kem', name: 'Lưới thép & dây kẽm', description: 'Lưới thép B40, lưới hàn và dây kẽm gai cho hàng rào bảo vệ công trình.', products_count: 3 },
]

// ============ APPLICATIONS ============
export const fallbackApplications: Application[] = [
  { slug: 'phan-cach-loc', name: 'Phân cách, lọc', description: 'Ngăn cách các lớp đất, lọc và ngăn vật liệu hạt mịn bị cuốn trôi.', products_count: 6 },
  { slug: 'thoat-nuoc', name: 'Thoát nước', description: 'Thu và thoát nước theo phương ngang, giảm áp lực nước lỗ rỗng.', products_count: 8 },
  { slug: 'gia-co-nen', name: 'Gia cố nền', description: 'Tăng sức chịu tải và giảm lún cho nền đất yếu.', products_count: 24 },
  { slug: 'chong-xoi-mon', name: 'Chống xói mòn', description: 'Bảo vệ bề mặt đất khỏi xói mòn do mưa và dòng chảy.', products_count: 7 },
  { slug: 'on-dinh-mai-doc', name: 'Ổn định mái dốc', description: 'Giữ ổn định mái dốc, tường chắn và bờ kè.', products_count: 16 },
  { slug: 'chong-tham', name: 'Chống thấm', description: 'Chống thấm tuyệt đối cho hồ chứa, kênh mương và bãi chôn lấp.', products_count: 2 },
  { slug: 'hang-rao-bao-ve', name: 'Hàng rào & bảo vệ', description: 'Hàng rào bảo vệ công trình, khu dân cư và an ninh.', products_count: 3 },
]

// ============ PRODUCTS ============
const img = (file: string) => `/images/products/${file}`
const proj = (file: string) => `/images/projects/${file}`

export const fallbackProducts: Product[] = [
  {
    slug: 'vai-kt-khong-det-art-12',
    code: 'ART 12',
    name: 'Vải địa kỹ thuật không dệt 12 kN/m',
    image: img('geotextile-roll.jpg'),
    strength_label: '12 kN/m',
    strength_min: '12.00',
    strength_max: '12.00',
    category: fallbackCategories[0],
    applications: [fallbackApplications[0], fallbackApplications[1]],
    description: 'Vải địa kỹ thuật không dệt xuyên kim, làm từ sợi polyester (PET) hoặc polypropylene (PP) nguyên sinh, được liên kết bằng phương pháp xuyên kim cơ học. Bề mặt vải có cấu trúc sợi ngẫu nhiên, đồng đều, đảm bảo tính thấm nước cao theo phương vuông góc mặt vải và giữ đất tốt theo phương tiếp tuyến. Ứng dụng: phân cách nền đường, bảo vệ mái dốc, lọc nước, gia cố nền đất yếu. Sản phẩm đạt tiêu chuẩn TCVN 9844:2013, ISO 9001:2015, tiêu chuẩn châu Âu EN.',
    specs: [
      { icon: 'scale', label: 'Khối lượng đơn vị', value: '120 g/m² (±10%)' },
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '12 kN/m' },
      { icon: 'water_drop', label: 'Hệ số thấm', value: '≥ 1.5 × 10⁻³ m/s' },
      { icon: 'straighten', label: 'Độ giãn dài', value: '40 – 80%' },
      { icon: 'aspect_ratio', label: 'Chiều rộng cuộn', value: '4.0 m' },
    ],
    images: [{ image: img('geotextile-roll.jpg'), alt: 'Vải ART 12 cuộn' }],
  },
  {
    slug: 'vai-kt-khong-det-art-25',
    code: 'ART 25',
    name: 'Vải địa kỹ thuật không dệt 25 kN/m',
    image: img('geotextile-roll.jpg'),
    strength_label: '25 kN/m',
    strength_min: '25.00',
    strength_max: '25.00',
    category: fallbackCategories[0],
    applications: [fallbackApplications[2], fallbackApplications[0]],
    description: 'Vải không dệt cường độ cao 25 kN/m, dùng cho gia cố nền đường, làm lớp phân cách dưới đáy móng, bảo vệ lớp chống thấm HDPE trong bãi rác. Sản phẩm đạt TCVN 9844:2013, ISO 9001:2015, tiêu chuẩn EN, ASTM.',
    specs: [
      { icon: 'scale', label: 'Khối lượng đơn vị', value: '250 g/m² (±10%)' },
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '25 kN/m' },
      { icon: 'water_drop', label: 'Hệ số thấm', value: '≥ 1.0 × 10⁻³ m/s' },
    ],
  },
  {
    slug: 'vai-kt-khong-det-art-30',
    code: 'ART 30',
    name: 'Vải địa kỹ thuật không dệt 30 kN/m',
    image: img('geotextile-roll.jpg'),
    strength_label: '30 kN/m',
    strength_min: '30.00',
    strength_max: '30.00',
    category: fallbackCategories[0],
    applications: [fallbackApplications[2]],
    description: 'Vải cường độ cao 30 kN/m, dùng cho nền đường giao thông, sân bay và khu công nghiệp.',
    specs: [
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '30 kN/m' },
    ],
  },
  {
    slug: 'vai-kt-khong-det-art-40',
    code: 'ART 40',
    name: 'Vải địa kỹ thuật không dệt 40 kN/m',
    image: img('geotextile-roll.jpg'),
    strength_label: '40 kN/m',
    strength_min: '40.00',
    strength_max: '40.00',
    category: fallbackCategories[0],
    applications: [fallbackApplications[2], fallbackApplications[3]],
    description: 'Vải cường độ rất cao 40 kN/m, dùng cho nền đường cao tốc, sân bay, bãi chứa container, kè biển.',
    specs: [
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '40 kN/m' },
      { icon: 'water_drop', label: 'Hệ số thấm', value: '≥ 8.0 × 10⁻⁴ m/s' },
    ],
  },
  {
    slug: 'vai-kt-khong-det-art-90',
    code: 'ART 90',
    name: 'Vải địa kỹ thuật không dệt 90 kN/m',
    image: img('geotextile-roll.jpg'),
    strength_label: '90 kN/m',
    strength_min: '90.00',
    strength_max: '90.00',
    category: fallbackCategories[0],
    applications: [fallbackApplications[2]],
    description: 'Vải cường độ siêu cao 90 kN/m, dùng cho nền đường cao tốc và đường sắt.',
    specs: [
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '90 kN/m' },
    ],
  },
  {
    slug: 'vai-kt-det-get-20',
    code: 'GET 20',
    name: 'Vải địa kỹ thuật dệt 20 kN/m',
    image: img('geotextile-roll.jpg'),
    strength_label: '20 kN/m',
    strength_min: '20.00',
    strength_max: '20.00',
    category: fallbackCategories[1],
    applications: [fallbackApplications[0], fallbackApplications[2]],
    description: 'Vải địa kỹ thuật dệt từ sợi polypropylene (PP), cường độ 20 kN/m, ứng dụng làm lớp phân cách dưới nền đường, gia cố nền yếu.',
    specs: [
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '20 kN/m' },
    ],
  },
  {
    slug: 'vai-kt-det-get-100',
    code: 'GET 100',
    name: 'Vải địa kỹ thuật dệt 100 kN/m',
    image: img('geotextile-roll.jpg'),
    strength_label: '100 kN/m',
    strength_min: '100.00',
    strength_max: '100.00',
    category: fallbackCategories[1],
    applications: [fallbackApplications[2], fallbackApplications[4]],
    description: 'Vải dệt cường độ cao 100 kN/m, dùng cho gia cố nền đường và ổn định mái dốc.',
    specs: [
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '100 kN/m' },
    ],
  },
  {
    slug: 'luoi-dia-ky-thuat-geogrid',
    code: 'GG 30 ÷ GG 50',
    name: 'Lưới địa kỹ thuật Geogrid',
    image: img('industrial-1.jpg'),
    strength_label: '30 – 50 kN/m',
    strength_min: '30.00',
    strength_max: '50.00',
    category: fallbackCategories[2],
    applications: [fallbackApplications[2], fallbackApplications[4]],
    description: 'Lưới địa kỹ thuật hai trục làm từ sợi polyester phủ PVC, ứng dụng gia cố nền đường đắp cao, tường chắn MSE, mái dốc.',
    specs: [
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '30 – 50 kN/m' },
      { icon: 'straighten', label: 'Độ giãn dài tại max', value: '≤ 12%' },
    ],
  },
  {
    slug: 'tham-3d-chong-xoi-mon',
    code: 'ECM 3D',
    name: 'Thảm 3D chống xói mòn',
    image: img('gabion-1.jpg'),
    strength_label: '5 – 8 kN/m',
    strength_min: '5.00',
    strength_max: '8.00',
    category: fallbackCategories[3],
    applications: [fallbackApplications[3], fallbackApplications[4]],
    description: 'Thảm 3D kết hợp lưới và sợi nylon, dùng phủ mái dốc kết hợp phun hạt giống cỏ để chống xói mòn, ổn định mái dốc.',
    specs: [
      { icon: 'scale', label: 'Khối lượng đơn vị', value: '350 – 500 g/m²' },
      { icon: 'compress', label: 'Độ bền kéo', value: '5 – 8 kN/m' },
    ],
  },
  {
    slug: 'ro-da-gabion',
    code: 'GAB 1x1x1',
    name: 'Rọ đá (Gabion)',
    image: img('gabion-1.jpg'),
    strength_label: 'Dây 2.7 – 3.0 mm',
    strength_min: '2.70',
    strength_max: '3.00',
    category: fallbackCategories[4],
    applications: [fallbackApplications[3], fallbackApplications[4]],
    description: 'Rọ đá mạ kẽm nhúng nóng, kích thước 1×1×1 m, 2×1×1 m, mắt lưới 80×100 mm. Dùng kè bờ, chống sạt lở, xây tường chắn trọng lực.',
    specs: [
      { icon: 'aspect_ratio', label: 'Kích thước', value: '1×1×1 m hoặc 2×1×1 m' },
      { icon: 'grid_on', label: 'Mắt lưới', value: '80 × 100 mm' },
      { icon: 'shield', label: 'Mạ kẽm', value: '≥ 250 g/m²' },
    ],
  },
  {
    slug: 'day-kem-gai',
    code: 'BARB 2',
    name: 'Dây kẽm gai',
    image: img('industrial-1.jpg'),
    strength_label: 'Dây Ø 2.5 – 3.0 mm',
    strength_min: '2.50',
    strength_max: '3.00',
    category: fallbackCategories[7],
    applications: [fallbackApplications[6]],
    description: 'Dây kẽm gai mạ kẽm nhúng nóng, dùng làm hàng rào bảo vệ công trình, kho bãi, trang trại.',
    specs: [
      { icon: 'shield', label: 'Đường kính dây', value: '2.5 – 3.0 mm' },
    ],
  },
  {
    slug: 'luoi-thep-han',
    code: 'WELD 50/50',
    name: 'Lưới thép hàn',
    image: img('industrial-1.jpg'),
    strength_label: 'Dây 3.0 – 4.0 mm',
    strength_min: '3.00',
    strength_max: '4.00',
    category: fallbackCategories[7],
    applications: [fallbackApplications[6]],
    description: 'Lưới thép hàn mạ kẽm, ô 50×50 mm, dây 3.0–4.0 mm, dùng làm hàng rào, kho lưu trữ, bảo vệ công trình.',
    specs: [
      { icon: 'grid_on', label: 'Mắt lưới', value: '50 × 50 mm' },
      { icon: 'shield', label: 'Đường kính dây', value: '3.0 – 4.0 mm' },
    ],
  },
  {
    slug: 'mang-hdpe-2mm',
    code: 'HDPE-2.0',
    name: 'Màng chống thấm HDPE 2.0 mm',
    image: img('industrial-1.jpg'),
    strength_label: 'Độ dày 2.0 mm',
    strength_min: '2.00',
    strength_max: '2.00',
    category: fallbackCategories[6],
    applications: [fallbackApplications[5]],
    description: 'Màng chống thấm HDPE độ dày 2.0 mm, màu đen, dùng lót đáy bãi rác, hồ chứa nước, kênh dẫn. Tiêu chuẩn GRI GM13, ASTM.',
    specs: [
      { icon: 'layers', label: 'Độ dày', value: '2.0 mm' },
      { icon: 'compress', label: 'Cường độ chịu kéo', value: '≥ 27 MPa' },
      { icon: 'shield', label: 'Kháng UV', value: '≥ 20 năm' },
    ],
  },
  {
    slug: 'bang-tham-gcl',
    code: 'GCL',
    name: 'Băng thấm Bentonite GCL',
    image: img('industrial-1.jpg'),
    strength_label: 'Bentonite 5.000 g/m²',
    strength_min: '5000',
    strength_max: '5000',
    category: fallbackCategories[5],
    applications: [fallbackApplications[5]],
    description: 'Băng thấm tổng hợp GCL gồm 2 lớp vải địa kỹ thuật và lõi bentonite, dùng thay thế đất sét trong các công trình chống thấm.',
    specs: [
      { icon: 'scale', label: 'Khối lượng bentonite', value: '5.000 g/m²' },
    ],
  },
]

// ============ PROJECTS ============
export const fallbackProjects: Project[] = [
  {
    slug: 'cao-toc-bac-bac-quang-nam',
    name: 'Đường cao tốc Bắc Bắc — Quảng Nam',
    location: 'Quảng Nam',
    period: '2024 - 2025',
    area: '50.000 m²',
    hero_image: proj('highway-1.jpg'),
    desc_image: proj('highway-2.jpg'),
    description: 'Cung cấp vải địa kỹ thuật ART 12, ART 25, ART 40 và lưới địa kỹ thuật GET 80 cho dự án đường cao tốc Bắc Bắc — Quảng Nam. Tổng khối lượng vải cung cấp hơn 250.000 m². Sản phẩm đạt TCVN 9844:2013, ISO 9001:2015.',
    materials: [
      { name: 'Vải ART 25', detail: '180.000 m², phân cách nền đường', image: img('geotextile-roll.jpg') },
      { name: 'Vải ART 40', detail: '70.000 m², khu vực nền đắp cao', image: img('geotextile-roll.jpg') },
    ],
    gallery: [
      { image: proj('highway-1.jpg'), alt: 'Cao tốc Bắc Bắc - Quảng Nam' },
      { image: proj('highway-2.jpg'), alt: 'Thi công nền đường' },
    ],
  },
  {
    slug: 'ho-thuy-tien-thua-thien-hue',
    name: 'Hồ Thuỷ Tiên — Thừa Thiên Huế',
    location: 'Thừa Thiên Huế',
    period: '2023 - 2024',
    area: '30.000 m²',
    hero_image: proj('lake-1.jpg'),
    desc_image: proj('lake-1.jpg'),
    description: 'Cung cấp màng chống thấm HDPE 1.5 mm, băng thấm GCL và thảm 3D chống xói mòn cho công trình hồ Thuỷ Tiên. Đảm bảo tiêu chuẩn chống thấm GRI GM13.',
    materials: [
      { name: 'Màng HDPE 1.5 mm', detail: '25.000 m², lót chống thấm đáy hồ', image: img('industrial-1.jpg') },
      { name: 'Thảm 3D', detail: '12.000 m², phủ mái dốc', image: img('gabion-1.jpg') },
    ],
    gallery: [
      { image: proj('lake-1.jpg'), alt: 'Hồ Thuỷ Tiên' },
    ],
  },
  {
    slug: 'khu-cong-nghiep-ha-noi',
    name: 'Khu công nghiệp Hà Nội',
    location: 'Hà Nội',
    period: '2024',
    area: '100.000 m²',
    hero_image: proj('highway-1.jpg'),
    desc_image: proj('highway-2.jpg'),
    description: 'Cung cấp vải địa kỹ thuật ART 12, ART 25 cho hệ thống thoát nước và gia cố nền toàn khu công nghiệp. Tổng khối lượng 350.000 m².',
    materials: [
      { name: 'Vải ART 25', detail: '250.000 m², gia cố nền', image: img('geotextile-roll.jpg') },
      { name: 'Vải ART 12', detail: '100.000 m², thoát nước', image: img('geotextile-roll.jpg') },
    ],
    gallery: [
      { image: proj('highway-1.jpg'), alt: 'KCN Hà Nội' },
    ],
  },
  {
    slug: 'khu-do-thi-xanh-thu-duc',
    name: 'Khu đô thị xanh — Thủ Đức',
    location: 'Thủ Đức, TP.HCM',
    period: '2023 - 2025',
    area: '80.000 m²',
    hero_image: proj('city-1.jpg'),
    desc_image: proj('city-1.jpg'),
    description: 'Cung cấp vải địa kỹ thuật ART 12, ART 25 và lưới B40 cho hệ thống thoát nước và hàng rào bảo vệ toàn khu đô thị. Tổng khối lượng vải 240.000 m², lưới B40 18.000 m².',
    materials: [
      { name: 'Vải ART 12', detail: '180.000 m², thoát nước', image: img('geotextile-roll.jpg') },
      { name: 'Lưới B40', detail: '18.000 m², hàng rào', image: img('gabion-1.jpg') },
    ],
    gallery: [
      { image: proj('city-1.jpg'), alt: 'KĐT xanh Thủ Đức' },
    ],
  },
  {
    slug: 'san-bay-quoc-te-long-thanh',
    name: 'Sân Bay quốc tế Long Thành — Đồng Nai',
    location: 'Đồng Nai',
    period: '2024 - 2026',
    area: '200.000 m²',
    hero_image: proj('airport-1.jpg'),
    desc_image: proj('airport-1.jpg'),
    description: 'Cung cấp vải địa kỹ thuật ART 12, ART 25 và băng thấm GCL phục vụ thi công hạ tầng kỹ thuật và các tuyến đường công vụ của sân bay Long Thành giai đoạn 1. Tổng giá trị cung cấp đạt hơn 18 tỷ đồng.',
    materials: [
      { name: 'Vải ART 25', detail: '210.000 m², phân cách nền đường', image: img('geotextile-roll.jpg') },
      { name: 'Băng thấm GCL', detail: '8.500 m², khu vực hồ điều hòa', image: img('industrial-1.jpg') },
    ],
    gallery: [
      { image: proj('airport-1.jpg'), alt: 'Sân bay Long Thành' },
    ],
  },
  {
    slug: 'cau-cai-mep-thi-vai',
    name: 'Cầu cảng Cái Mép — Thị Vải',
    location: 'Bà Rịa - Vũng Tàu',
    period: '2019 - 2021',
    area: '1,2 km bến cảng',
    hero_image: proj('bridge-1.jpg'),
    desc_image: proj('bridge-1.jpg'),
    description: 'Cung cấp toàn bộ hệ thống vải địa kỹ thuật, màng chống thấm HDPE và rọ đá cho gói thầu 5A — bến cảng container Cái Mép — Thị Vải. Vải ART 40 dùng gia cố nền, màng HDPE 2.0 mm lót chống thấm, rọ đá kè bảo vệ mái dốc taluy âm.',
    materials: [
      { name: 'Vải ART 40', detail: '95.000 m², gia cố nền', image: img('geotextile-roll.jpg') },
      { name: 'Màng HDPE 2.0 mm', detail: '18.000 m², lót chống thấm', image: img('industrial-1.jpg') },
      { name: 'Rọ đá 2×1×1', detail: '4.200 cái, kè bảo vệ', image: img('gabion-1.jpg') },
    ],
    gallery: [
      { image: proj('bridge-1.jpg'), alt: 'Cầu cảng Cái Mép' },
    ],
  },
]

// ============ NEWS ============
export const fallbackNewsCategories = [
  { slug: 'tin-tuc', name: 'Tin tức' },
  { slug: 'ky-thuat', name: 'Kỹ thuật' },
  { slug: 'su-kien', name: 'Sự kiện' },
  { slug: 'du-an', name: 'Dự án' },
]

export const fallbackNews: NewsItem[] = [
  {
    slug: 'chon-thanh-dat-doi-tac-hock',
    title: 'CHƠN THÀNH đạt chứng nhận đối tác uỷ quyền HOCK Technology',
    excerpt: 'CHƠN THÀNH vinh dự được công nhận là nhà phân phối uỷ quyền chính thức của HOCK Technology tại thị trường Việt Nam cho dòng sản phẩm vải địa kỹ thuật ARITEX.',
    image: '/images/news/workshop.jpg',
    published_at: '2024-10-05T00:00:00+00:00',
    category: { slug: 'tin-tuc', name: 'Tin tức' },
    content: 'Ngày 05/10/2024, Công ty TNHH Dịch vụ và Thương mại CHƠN THÀNH chính thức được HOCK Technology Co.,Ltd (Malaysia) cấp giấy chứng nhận nhà phân phối uỷ quyền chính thức cho dòng sản phẩm vải địa kỹ thuật ARITEX tại thị trường Việt Nam.\n\nGiấy chứng nhận có số CTCO-AT-VN04-20220905-01, cấp ngày 30/10/2023, đánh dấu mốc quan trọng trong quan hệ hợp tác chiến lược giữa hai bên, mở ra cơ hội cung cấp sản phẩm chất lượng cao đến thị trường Việt Nam.\n\nHOCK Technology là nhà sản xuất vải địa kỹ thuật lớn nhất châu Á, công suất 20.000 tấn/năm, hệ thống phân phối trên 30 quốc gia. Sản phẩm ARITEX đã đạt chứng nhận ISO 9001, tiêu chuẩn EN ISO 10319 và ASTM.',
  },
  {
    slug: 'hoi-thao-ung-dung-geogrid-2024',
    title: 'Hội thảo ứng dụng lưới địa kỹ thuật trong xây dựng hạ tầng',
    excerpt: 'CHƠN THÀNH tổ chức hội thảo chuyên đề về ứng dụng lưới địa kỹ thuật cốt sợi thủy tinh trong gia cố mặt đường bê tông nhựa.',
    image: '/images/news/workshop.jpg',
    published_at: '2024-07-20T00:00:00+00:00',
    category: { slug: 'su-kien', name: 'Sự kiện' },
    content: 'Ngày 20/07/2024, tại Hà Nội, CHƠN THÀNH phối hợp với Hội Cơ học đất & Địa kỹ thuật Việt Nam tổ chức hội thảo chuyên đề: "Ứng dụng lưới địa kỹ thuật cốt sợi thủy tinh trong gia cố mặt đường bê tông nhựa".\n\nHội thảo thu hút hơn 80 kỹ sư từ các ban quản lý dự án, tổng thầu và công ty tư vấn thiết kế trên cả nước. Các chuyên đề được trình bày gồm: tiêu chuẩn thiết kế, giải pháp kỹ thuật, kinh nghiệm thực tiễn từ các dự án lớn.',
  },
  {
    slug: 'iso-9001-tai-cap-2023',
    title: 'CHƠN THÀNH tái cấp ISO 9001:2015 lần thứ 4',
    excerpt: 'Ngày 13/09/2023, CHƠN THÀNH chính thức nhận chứng nhận ISO 9001:2015 tái cấp từ NQA (UKAS - Vương quốc Anh), đánh dấu cột mốc 18 năm duy trì hệ thống quản lý chất lượng quốc tế.',
    image: '/images/certificates/iso-9001.jpg',
    published_at: '2023-09-13T00:00:00+00:00',
    category: { slug: 'tin-tuc', name: 'Tin tức' },
    content: 'Ngày 13/09/2023, tại văn phòng công ty, đại diện NQA Việt Nam đã trao chứng nhận ISO 9001:2015 tái cấp cho Công ty TNHH Dịch vụ và Thương mại CHƠN THÀNH. Buổi lễ có sự tham dự của Ban Giám đốc, đội ngũ QC và đại diện hai nhà máy.\n\nĐây là lần tái cấp thứ 4 liên tiếp, khẳng định cam kết của CHƠN THÀNH trong việc duy trì hệ thống quản lý chất lượng ổn định, đáp ứng yêu cầu ngày càng cao của khách hàng.',
  },
  {
    slug: 'thi-truong-vai-kt-2024',
    title: 'Thị trường vải địa kỹ thuật 2024: Xu hướng và triển vọng',
    excerpt: 'Theo báo cáo của Hiệp hội Vật liệu Xây dựng Việt Nam, thị trường vải địa kỹ thuật 2024 ước đạt 85 triệu USD, tăng 15% so với 2023 nhờ các dự án hạ tầng giao thông trọng điểm.',
    image: '/images/projects/highway-1.jpg',
    published_at: '2024-03-15T00:00:00+00:00',
    category: { slug: 'tin-tuc', name: 'Tin tức' },
    content: 'Báo cáo phân tích thị trường vải địa kỹ thuật 2024 của Hiệp hội Vật liệu Xây dựng Việt Nam (VABM) cho thấy, doanh thu toàn ngành ước đạt 85 triệu USD, tăng 15% so với năm 2023. Trong đó, vải không dệt chiếm 65%, vải dệt 20%, lưới địa kỹ thuật và màng HDPE chiếm 15%.\n\nXu hướng nổi bật năm 2024 gồm: (1) tăng cường sử dụng vật liệu tái chế; (2) áp dụng tiêu chuẩn khí thải carbon thấp; (3) ứng dụng trong nông nghiệp công nghệ cao.',
  },
  {
    slug: 'ky-thuat-thi-cong-vai-kt',
    title: 'Kỹ thuật thi công vải địa kỹ thuật trong gia cố nền đường',
    excerpt: 'Bài viết hướng dẫn chi tiết quy trình thi công vải địa kỹ thuật trong gia cố nền đường theo TCVN 9844:2013, đảm bảo chất lượng và tuổi thọ công trình.',
    image: '/images/projects/highway-2.jpg',
    published_at: '2024-01-30T00:00:00+00:00',
    category: { slug: 'ky-thuat', name: 'Kỹ thuật' },
    content: 'Quy trình thi công vải địa kỹ thuật trong gia cố nền đường gồm 6 bước:\n\n1. Chuẩn bị mặt bằng: dọn dẹp, đầm chặt nền đất hiện hữu đạt độ chặt K ≥ 0.95.\n2. Trải vải: trải cuộn vải theo phương dọc trục đường, đảm bảo vải phẳng không nhăn.\n3. Nối chồng: các mép vải chồng mí 30-50 cm theo phương ngang, 50-100 cm theo phương dọc.\n4. Khoan chặt: dùng ghim thép hoặc túi cát cố định mép vải.\n5. Đổ đá dăm: đổ lớp đá dăm dày 20-30 cm lên trên vải, đầm chặt bằng lu rung.\n6. Nghiệm thu: kiểm tra độ chặt, cao độ, độ bằng phẳng.\n\nTuân thủ TCVN 9844:2013 và tiêu chuẩn EN ISO 10319.',
  },
  {
    slug: 'xuat-khau-campuchia-2024',
    title: 'CHƠN THÀNH xuất khẩu lô hàng 5.000 m² rọ đá sang Campuchia',
    excerpt: 'Ngày 15/06/2024, lô hàng rọ đá mạ kẽm 5.000 m² đã được xuất khẩu sang Campuchia phục vụ dự án kè sông Mekong do Tập đoàn Cấp nước Campuchia làm chủ đầu tư.',
    image: '/images/projects/bridge-1.jpg',
    published_at: '2024-06-15T00:00:00+00:00',
    category: { slug: 'tin-tuc', name: 'Tin tức' },
    content: 'Ngày 15/06/2024, lô hàng gồm 1.250 rọ đá kích thước 2×1×1 m, mắt lưới 80×100 mm, dây 2.7 mm mạ kẽm nhúng nóng theo tiêu chuẩn ASTM A975 đã được xuất khẩu sang Campuchia. Toàn bộ hồ sơ CO/CQ đã được phía Campuchia xác nhận tại cửa khẩu Mộc Bài.\n\nĐây là lô hàng xuất khẩu thứ 8 trong vòng 6 tháng qua của CHƠN THÀNH sang thị trường Campuchia, Lào và Myanmar.',
  },
]

// ============ FAQS ============
export const fallbackFaqs: FaqItem[] = [
  {
    question: 'CHƠN THÀNH cung cấp những loại vật liệu địa kỹ thuật nào?',
    answer: 'Chúng tôi cung cấp đầy đủ các dòng sản phẩm: vải địa kỹ thuật không dệt (ART 12 – ART 280), vải địa kỹ thuật dệt (GET 5 – GET 500), lưới địa kỹ thuật, thảm 3D chống xói mòn, rọ đá, băng thấm GCL, màng chống thấm HDPE, lưới thép B40 và dây kẽm gai.',
  },
  {
    question: 'Sản phẩm có được kiểm định chất lượng không?',
    answer: 'Tất cả sản phẩm của CHƠN THÀNH đều có chứng nhận CO/CQ đầy đủ. Công ty đạt chứng nhận ISO 9001:2015, sản phẩm phù hợp TCVN 9844:2013 và là nhà phân phối uỷ quyền chính thức của HOCK Technology.',
  },
  {
    question: 'Làm thế nào để nhận báo giá sản phẩm?',
    answer: 'Quý khách vui lòng điền thông tin vào form liên hệ hoặc gọi hotline 0909 292 530. Đội ngũ kỹ sư của chúng tôi sẽ tư vấn và gửi báo giá trong vòng 24 giờ.',
  },
  {
    question: 'Thời gian giao hàng trung bình là bao lâu?',
    answer: 'Chúng tôi cam kết thời gian giao hàng tối đa 7 ngày trên toàn quốc đối với các sản phẩm có sẵn trong kho, bằng đội xe tải 2.5–18 tấn đến tận chân công trình.',
  },
  {
    question: 'CHƠN THÀNH có hỗ trợ vận chuyển hàng đến công trình không?',
    answer: 'Chúng tôi hỗ trợ vận chuyển đến chân công trình trên toàn quốc và xuất khẩu sang Campuchia, Lào, Myanmar. Chi phí vận chuyển được tính toán dựa trên khối lượng đơn hàng và khoảng cách địa lý.',
  },
  {
    question: 'Các sản phẩm của CHƠN THÀNH có bảo hành không?',
    answer: 'Tất cả sản phẩm đều được bảo hành theo tiêu chuẩn của nhà sản xuất, thông thường từ 12–24 tháng kể từ ngày giao hàng, tuỳ theo từng dòng sản phẩm cụ thể.',
  },
  {
    question: 'Tôi có thể đến tham quan kho bãi và văn phòng không?',
    answer: 'Hoàn toàn có thể. Quý khách vui lòng liên hệ trước để đặt lịch hẹn, đội ngũ chúng tôi sẽ sẵn sàng đón tiếp tại 416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh.',
  },
  {
    question: 'CHƠN THÀNH có hỗ trợ kỹ thuật tại công trình không?',
    answer: 'Có. Chúng tôi cung cấp dịch vụ tư vấn kỹ thuật miễn phí từ giai đoạn thiết kế cơ sở đến khi hoàn thiện, hỗ trợ giám sát thi công tại công trình, đảm bảo vật liệu được lắp đặt đúng kỹ thuật.',
  },
]

// ============ CERTIFICATES ============
export const fallbackCertificates: Certificate[] = [
  {
    slug: 'ho-so-nang-luc-2026',
    name: 'Hồ sơ năng lực 2026',
    description: 'Hồ sơ năng lực (Profile Book) công ty CHƠN THÀNH phiên bản 2026, bao gồm các dự án tiêu biểu, năng lực sản xuất và các chứng nhận chất lượng.',
    image: '/images/certificates/authorization-hock.jpg', // Có thể dùng tạm ảnh này
    file: '/storage/profile/ho-so-nang-luc.pdf',
  },
  {
    slug: 'giay-uy-quyen-hock',
    name: 'Giấy uỷ quyền phân phối HOCK',
    description: 'Giấy uỷ quyền số CTCO-AT-VN04-20220905-01 cấp ngày 30/10/2023. CHƠN THÀNH là nhà phân phối uỷ quyền chính thức của HOCK Technology Co.,Ltd cho dòng sản phẩm vải địa kỹ thuật ARITEX tại thị trường Việt Nam.',
    image: '/images/certificates/authorization-hock.jpg',
    file: null,
  },
  {
    slug: 'iso-9001-2015',
    name: 'Chứng nhận ISO 9001:2015',
    description: 'Hệ thống quản lý chất lượng đạt chuẩn ISO 9001:2015, chứng nhận số 120895 do NQA chứng nhận (UKAS - Vương quốc Anh), hiệu lực 13/09/2023 – 13/09/2026. Áp dụng cho hoạt động sản xuất và phân phối vật liệu địa kỹ thuật.',
    image: '/images/certificates/iso-9001.jpg',
    file: null,
  },
  {
    slug: 'giay-chung-nhan-dang-ky-kinh-doanh',
    name: 'Giấy chứng nhận đăng ký kinh doanh',
    description: 'CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH, mã số doanh nghiệp 0303792837, đăng ký lần đầu 11/05/2005, đăng ký thay đổi lần thứ 15 ngày 15/10/2025 do Sở Kế hoạch và Đầu tư TP.HCM cấp. Vốn điều lệ 20.000.000.000 đồng.',
    image: '/images/certificates/co-cq.jpg',
    file: null,
  },
  {
    slug: 'hop-chuan-tcvn-9844',
    name: 'Giấy chứng nhận hợp chuẩn TCVN 9844:2013',
    description: 'Sản phẩm vải địa kỹ thuật phù hợp tiêu chuẩn quốc gia TCVN 9844:2013 về vải địa kỹ thuật - phương pháp thử và yêu cầu kỹ thuật, đồng thời đáp ứng các tiêu chuẩn tương đương của châu Âu (EN ISO 10319).',
    image: '/images/certificates/hop-chuan.jpg',
    file: null,
  },
  {
    slug: 'iso-14001-2015',
    name: 'Chứng nhận ISO 14001:2015',
    description: 'Hệ thống quản lý môi trường theo tiêu chuẩn ISO 14001:2015, thể hiện cam kết bảo vệ môi trường và phát triển bền vững trong toàn bộ hoạt động sản xuất của công ty.',
    image: '/images/certificates/iso-9001.jpg',
    file: null,
  },
  {
    slug: 'chung-nhan-hock-aritex',
    name: 'Chứng nhận HOCK ARITEX',
    description: 'Sản phẩm vải địa kỹ thuật ARITEX phân phối bởi CHƠN THÀNH đạt tiêu chuẩn chất lượng HOCK Technology - nhà sản xuất lớn nhất châu Á với công suất 20.000 tấn/năm.',
    image: '/images/certificates/authorization-hock.jpg',
    file: null,
  },
]

// ============ TIMELINE ============
export const fallbackTimeline: AboutTimeline[] = [
  { year: '2005 - Thành lập', description: 'Thành lập CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH, mã số doanh nghiệp 0303792837 do Sở KH&ĐT TP.HCM cấp.' },
  { year: '2007 - Nhà máy Rọ đá Á Châu', description: 'Khánh thành nhà máy sản xuất rọ đá tại Hóc Môn, TP.HCM, công suất 3.000 tấn/năm - khởi đầu chuỗi sản xuất của công ty.' },
  { year: '2013 - Hợp chuẩn TCVN 9844', description: 'Sản phẩm vải địa kỹ thuật đạt chứng nhận hợp chuẩn TCVN 9844:2013 và tiêu chuẩn châu Âu EN ISO 10319.' },
  { year: '2015 - Nhà máy Lưới thép Tiên Phong', description: 'Khánh thành nhà máy Lưới thép Tiên Phong tại Đà Nẵng, công suất 5.000 tấn/năm. Tổng công suất 2 nhà máy đạt 8.000 tấn/năm.' },
  { year: '2020 - Đối tác HOCK Technology', description: 'Trở thành nhà phân phối uỷ quyền chính thức dòng sản phẩm vải địa kỹ thuật ARITEX của HOCK Technology tại Việt Nam.' },
  { year: '2023 - Tái cấp ISO 9001:2015', description: 'Hệ thống quản lý chất lượng đạt chuẩn ISO 9001:2015 do NQA (UKAS - Vương quốc Anh) chứng nhận, hiệu lực đến 13/09/2026.' },
  { year: '2024 - Sân bay Long Thành', description: 'Cung cấp vật liệu địa kỹ thuật cho dự án Sân bay quốc tế Long Thành giai đoạn 1, tổng giá trị hợp đồng trên 18 tỷ đồng.' },
  { year: 'Hiện tại', description: 'Hai nhà máy, công suất 8.000 tấn/năm, 18 nhân sự chuyên nghiệp, đội xe 7 chiếc (2.5–18 tấn), xuất khẩu sang Campuchia, Lào, Myanmar.' },
]

// ============ HOME STATS ============
export const fallbackStats: HomeStat[] = [
  { icon: 'workspace_premium', value: `${getYearsOfExperience()}+`, label: 'Năm kinh nghiệm' },
  { icon: 'factory', value: '2', label: 'Nhà máy sản xuất' },
  { icon: 'warehouse', value: '8,000', label: 'Tấn sản phẩm/năm' },
  { icon: 'award', value: 'ISO 9001', label: 'Chứng nhận ISO 9001:2015' },
  { icon: 'users', value: '18', label: 'Nhân sự chuyên nghiệp' },
  { icon: 'truck', value: '7', label: 'Xe tải 2.5–18 tấn' },
]

// ============ WHY CHOOSE US ============
export const fallbackWhyChooseUs: WhyChooseUsItem[] = [
  { icon: 'verified', title: 'Chất lượng đạt chuẩn', description: 'Sản phẩm sản xuất theo hệ thống quản lý chất lượng ISO 9001:2015, phù hợp TCVN 9844:2013 và tiêu chuẩn châu Âu.' },
  { icon: 'factory', title: 'Trực tiếp từ nhà máy', description: 'Giá cạnh tranh từ hai nhà máy: Rọ đá Á Châu (Hóc Môn) và Lưới Thép Tiên Phong (Đà Nẵng).' },
  { icon: 'support_agent', title: 'Hỗ trợ kỹ thuật miễn phí', description: 'Tư vấn lựa chọn vật liệu, hướng dẫn lắp đặt và thi công cho mọi dự án, mọi quy mô.' },
  { icon: 'local_shipping', title: 'Giao hàng toàn quốc', description: 'Đội xe tải 2.5–18 tấn giao hàng tận công trình, xuất khẩu sang Campuchia, Lào và Myanmar.' },
  { icon: 'workspace_premium', title: `Uy tín hơn ${getYearsOfExperience()} năm`, description: 'Hoạt động từ năm 2005, đồng hành cùng hàng nghìn công trình hạ tầng trên toàn quốc.' },
  { icon: 'engineering', title: 'Đội ngũ chuyên nghiệp', description: '18 nhân sự giàu kinh nghiệm, tư vấn và xử lý đơn hàng nhanh chóng, chính xác.' },
]
