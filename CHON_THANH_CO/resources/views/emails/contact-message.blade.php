<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu tư vấn từ website CHON THANH</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 4px 24px rgba(0,0,0,0.06);">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#1d4ed8;padding:28px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <h1 style="margin:0;color:#ffffff;font-size:20px;line-height:1.3;">YÊU CẦU TƯ VẤN TỪ WEBSITE</h1>
                                        <p style="margin:6px 0 0;color:#bfdbfe;font-size:13px;letter-spacing:1px;text-transform:uppercase;">CHON THANH Geosynthetics</p>
                                    </td>
                                    <td align="right" style="vertical-align:middle;">
                                        <span style="display:inline-block;background-color:#f97316;color:#ffffff;font-size:12px;font-weight:bold;padding:6px 14px;border-radius:999px;text-transform:uppercase;letter-spacing:0.5px;">Mới</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 24px;color:#374151;font-size:14px;line-height:1.6;">
                                Có một khách hàng vừa gửi yêu cầu tư vấn qua website. Vui lòng phản hồi trong thời gian sớm nhất để chăm sóc khách hàng tốt nhất.
                            </p>

                            {{-- Customer info --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                                <tr>
                                    <td style="background-color:#f8fafc;border-bottom:1px solid #e5e7eb;padding:12px 16px;font-size:12px;font-weight:bold;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.5px;">Thông tin khách hàng</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="40%" style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #f3f4f6;">Họ tên</td>
                                                <td style="padding:10px 16px;color:#111827;font-size:13px;font-weight:600;border-bottom:1px solid #f3f4f6;">{{ $contact->name }}</td>
                                            </tr>
                                            <tr>
                                                <td width="40%" style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #f3f4f6;">Điện thoại</td>
                                                <td style="padding:10px 16px;color:#111827;font-size:13px;font-weight:600;border-bottom:1px solid #f3f4f6;">
                                                    <a href="tel:{{ $contact->phone }}" style="color:#1d4ed8;text-decoration:none;">{{ $contact->phone }}</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="40%" style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #f3f4f6;">Email</td>
                                                <td style="padding:10px 16px;color:#111827;font-size:13px;font-weight:600;border-bottom:1px solid #f3f4f6;">
                                                    <a href="mailto:{{ $contact->email }}" style="color:#1d4ed8;text-decoration:none;">{{ $contact->email }}</a>
                                                </td>
                                            </tr>
                                            @if ($contact->company)
                                            <tr>
                                                <td width="40%" style="padding:10px 16px;color:#6b7280;font-size:13px;border-bottom:1px solid #f3f4f6;">Công ty</td>
                                                <td style="padding:10px 16px;color:#111827;font-size:13px;font-weight:600;border-bottom:1px solid #f3f4f6;">{{ $contact->company }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Products --}}
                            @if (($contact->products && count($contact->products)) || $contact->product)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-top:20px;">
                                <tr>
                                    <td style="background-color:#f8fafc;border-bottom:1px solid #e5e7eb;padding:12px 16px;font-size:12px;font-weight:bold;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.5px;">Sản phẩm quan tâm ({{ $contact->products ? count($contact->products) : 1 }})</td>
                                </tr>
                                <tr>
                                    <td style="padding:16px;">
                                        @if ($contact->products && count($contact->products))
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                @foreach ($contact->products as $product)
                                                <tr>
                                                    <td style="padding:6px 0;color:#111827;font-size:13px;">
                                                        <span style="display:inline-block;width:8px;height:8px;background-color:#f97316;border-radius:50%;margin-right:10px;vertical-align:middle;"></span>
                                                        {{ $product }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        @else
                                            <p style="margin:0;color:#111827;font-size:13px;">{{ $contact->product }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @endif

                            {{-- Message --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-top:20px;">
                                <tr>
                                    <td style="background-color:#f8fafc;border-bottom:1px solid #e5e7eb;padding:12px 16px;font-size:12px;font-weight:bold;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.5px;">Nội dung yêu cầu</td>
                                </tr>
                                <tr>
                                    <td style="padding:16px;color:#374151;font-size:14px;line-height:1.7;white-space:pre-line;">{{ $contact->message }}</td>
                                </tr>
                            </table>

                            {{-- CTA --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('admin.contacts.index') }}" style="display:inline-block;background-color:#f97316;color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none;padding:12px 32px;border-radius:8px;">Xử lý trong Admin Panel</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#f8fafc;border-top:1px solid #e5e7eb;padding:20px 32px;">
                            <p style="margin:0;color:#6b7280;font-size:12px;line-height:1.6;text-align:center;">
                                Email được gửi tự động từ website <strong>chonthanh.com.vn</strong><br>
                                Gửi lúc {{ $contact->created_at?->format('H:i d/m/Y') }} &middot; Vui lòng không trả lời email này.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
