<script setup lang="ts">
import PageHeader from '../components/PageHeader.vue'
import { t } from '../i18n'

const breadcrumbs = [{ label: t('nav.home'), to: '/' }, { label: 'Điều khoản dịch vụ' }]
</script>

<template>
  <div>
    <PageHeader :title="'Điều khoản dịch vụ'" :breadcrumbs="breadcrumbs" />
    <section class="max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-10 md:py-14">
      <h2 class="text-2xl font-bold mb-4">Điều khoản dịch vụ</h2>
      <section>
        <h3 class="text-xl font-semibold mb-2">1. Định nghĩa</h3>
        <ul class="list-disc list-inside mb-2">
          <li><strong>Website</strong>: https://chonthanhco.vn, bao gồm các trang và dịch vụ trực tuyến.</li>
          <li><strong>Người dùng</strong>: cá nhân hoặc tổ chức truy cập và sử dụng dịch vụ trên Website.</li>
          <li><strong>Dịch vụ</strong>: cung cấp thông tin sản phẩm, cho phép đặt hàng, tải tài liệu và liên hệ.</li>
        </ul>
        <h3 class="text-xl font-semibold mb-2 mt-4">2. Điều kiện sử dụng</h3>
        <p>Người dùng phải đủ 18 tuổi trở lên và không vi phạm pháp luật Việt Nam. Khi đăng ký tài khoản, người dùng cam kết cung cấp thông tin chính xác.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">3. Quyền sở hữu trí tuệ</h3>
        <p>Tất cả nội dung, hình ảnh, logo, thương hiệu trên Website là tài sản của CHÔN THÀNH và được bảo hộ bởi pháp luật sở hữu trí tuệ. Người dùng không được sao chép, sửa đổi, phân phối mà không có sự đồng ý bằng văn bản.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">4. Đặt hàng và thanh toán</h3>
        <ul class="list-disc list-inside mb-2">
          <li>Giá bán đã bao gồm thuế GTGT 10% và các phí liên quan.</li>
          <li>Thanh toán qua cổng thanh toán VNPay, ngân hàng hoặc chuyển khoản, đảm bảo an toàn thông tin.</li>
          <li>Hàng hóa sẽ được giao trong vòng 5‑7 ngày làm việc kể từ khi thanh toán thành công.</li>
        </ul>
        <h3 class="text-xl font-semibold mb-2 mt-4">5. Chính sách đổi trả và bảo hành</h3>
        <p>Khách hàng có quyền yêu cầu đổi trả trong vòng 7 ngày kể từ ngày nhận hàng nếu hàng bị lỗi kỹ thuật. Đối với các sản phẩm đã sử dụng, không chấp nhận đổi trả.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">6. Trách nhiệm của người dùng</h3>
        <p>Người dùng không được đăng tải nội dung vi phạm pháp luật, gây hại, hoặc xâm phạm quyền sở hữu trí tuệ của bên thứ ba. Người dùng chịu trách nhiệm pháp lý đối với mọi hành vi vi phạm.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">7. Giới hạn trách nhiệm</h3>
        <p>CHÔN THÀNH không chịu trách nhiệm gây ra thiệt hại do lỗi mạng, thiên tai, hành vi của bên thứ ba hoặc lỗi ngoài khả năng kiểm soát.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">8. Chấm dứt tài khoản</h3>
        <p>CHÔN THÀNH có quyền tạm dừng hoặc chấm dứt tài khoản người dùng khi vi phạm các Điều khoản này mà không cần bồi thường.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">9. Thay đổi Điều khoản</h3>
        <p>CHÔN THÀNH có thể sửa đổi Điều khoản bất kỳ lúc nào. Thay đổi sẽ được công bố trên trang này và có hiệu lực ngay sau khi đăng tải.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">10. Luật áp dụng & giải quyết tranh chấp</h3>
        <p>Giao dịch và các tranh chấp phát sinh sẽ được giải quyết theo pháp luật Việt Nam, tại Tòa án Nhân dân Thành phố Hồ Chí Minh.</p>
        <h3 class="text-xl font-semibold mb-2 mt-4">11. Liên hệ</h3>
        <p>Địa chỉ: 416A Đường CC2, Phường Sơn Kỳ, Quận Tân Phú, TP.HCM<br/>Email: support@chonthanhco.vn<br/>Điện thoại: 028.22 179 115</p>
        <p class="mt-4 text-sm text-gray-500">Cập nhật lần cuối: 08/09/2026</p>
      </section>
    </section>
  </div>
</template>

<style scoped>
/* Add any page‑specific styles here */
</style>
