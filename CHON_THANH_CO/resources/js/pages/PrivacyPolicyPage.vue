<script setup lang="ts">
import PageHeader from '../components/PageHeader.vue'
import { t } from '../i18n'

const breadcrumbs = [{ label: t('nav.home'), to: '/' }, { label: 'Chính sách bảo mật' }]
</script>

<template>
  <div>
    <PageHeader :title="'Chính sách bảo mật'" :breadcrumbs="breadcrumbs" />
    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14">
      <h2 class="text-2xl font-bold mb-4">Chính sách bảo mật</h2>
      <section>
        <h3 class="text-xl font-semibold mb-2">1. Giới thiệu</h3>
        <p>Công ty Cổ phần Dịch vụ CHÔN THÀNH (địa chỉ: 416A Đường CC2, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM) là người chịu trách nhiệm bảo vệ thông tin cá nhân của người dùng.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">2. Thu thập thông tin</h3>
        <ul class="list-disc list-inside mb-2">
          <li>Thông tin cá nhân khi đăng ký tài khoản: họ tên, email, số điện thoại.</li>
          <li>Thông tin phi cá nhân: địa chỉ IP, loại trình duyệt, thời gian truy cập, cookie.</li>
          <li>Thông tin giao dịch khi mua hàng: địa chỉ giao hàng, dữ liệu thanh toán.</li>
        </ul>
        <h3 class="text-xl font-semibold mb-2 mt-4">3. Mục đích sử dụng</h3>
        <p>Chúng tôi sử dụng dữ liệu để:</p>
        <ul class="list-disc list-inside mb-2">
          <li>Xử lý đơn hàng và cung cấp dịch vụ.</li>
          <li>Gửi thông báo, tin khuyến mãi (khi người dùng đã đồng ý).</li>
          <li>Cải thiện, phân tích và phát triển website.</li>
        </ul>
        <h3 class="text-xl font-semibold mb-2 mt-4">4. Thời gian lưu trữ</h3>
        <p>Dữ liệu cá nhân sẽ được lưu trữ tối đa 24 tháng kể từ ngày thu thập, hoặc cho đến khi không còn mục đích sử dụng.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">5. Quyền của người dùng</h3>
        <p>Người dùng có quyền yêu cầu truy cập, chỉnh sửa hoặc xóa dữ liệu cá nhân bằng cách gửi email tới <a href="mailto:privacy@chonthanhco.vn" class="text-primary underline">privacy@chonthanhco.vn</a>.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">6. Chia sẻ và chuyển giao</h3>
        <p>Chúng tôi không bán, cho thuê dữ liệu cá nhân cho bên thứ ba. Dữ liệu chỉ được chia sẻ với các đối tác cung cấp dịch vụ (cổng thanh toán, nhà vận chuyển) theo hợp đồng bảo mật.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">7. Bảo mật dữ liệu</h3>
        <p>Website sử dụng HTTPS, mã hoá dữ liệu tại nghỉ, tường lửa và kiểm tra bảo mật định kỳ.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">8. Cookie</h3>
        <p>Chúng tôi sử dụng cookie để lưu giỏ hàng và phân tích lưu lượng. Người dùng có thể tắt cookie trong cài đặt trình duyệt.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">9. Liên hệ</h3>
        <p>Thắc mắc hoặc khiếu nại về bảo mật, vui lòng liên hệ: <a href="mailto:privacy@chonthanhco.vn" class="text-primary underline">privacy@chonthanhco.vn</a> hoặc số điện thoại 028.22 179 115.</p>
        <p class="mt-4 text-sm text-gray-500">Cập nhật lần cuối: 08/09/2026</p>
      </section>
    </section>
  </div>
</template>

<style scoped>
/* Add any page‑specific styles here */
</style>
