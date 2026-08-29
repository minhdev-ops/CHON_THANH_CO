<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Phản hồi từ CHON THANH CO.</title>
</head>
<body>
    <h2 style="color:#1e3a5f;">Kính gửi {{ $contact->name }},</h2>

    <p>Cảm ơn bạn đã liên hệ với CHON THANH CO. qua website của chúng tôi. Nội dung phản hồi từ đội ngũ hỗ trợ:</p>

    <div style="background:#f5f7fa;border-left:4px solid #1e3a5f;padding:16px 20px;margin:16px 0;color:#333;">
        {!! nl2br(e($reply)) !!}
    </div>

    @if ($contact->product)
    <p style="color:#666;font-size:13px;">Liên quan đến: <strong>{{ $contact->product }}</strong></p>
    @endif

    <p>Nếu bạn cần thêm thông tin, đừng ngần ngại liên hệ lại với chúng tôi qua email hoặc điện thoại.</p>

    <p style="margin-top:32px;">
        Trân trọng,<br>
        <strong>CÔNG TY TNHH DỊCH VỤ VÀ THƯƠNG MẠI CHƠN THÀNH</strong><br>
        <span style="color:#666;font-size:13px;">416A Đường CC2, Phường Tây Thạnh, Thành Phố Hồ Chí Minh</span>
    </p>
</body>
</html>
