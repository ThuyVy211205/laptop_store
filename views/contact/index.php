<?php
$extraCss = ['contact.css'];
include ROOT_PATH . '/views/layouts/header.php';
?>

<!-- ─────────────────────────────────────────────────────────────
     BREADCRUMB
     ───────────────────────────────────────────────────────────── -->
<div class="tc-breadcrumb">
    <div class="tc-breadcrumb__inner">
        <a href="<?= SITE_URL ?>">Trang chủ</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
        <span>Liên hệ</span>
    </div>
</div>


<!-- ─────────────────────────────────────────────────────────────
     CONTACT SECTION
     ───────────────────────────────────────────────────────────── -->
<section class="tc-section" aria-labelledby="tc-heading">
    <div class="tc-container">

        <!-- ╔══════════════════════════════════════════════════════╗
             ║  MAIN CARD  (dark-navy left + white right)          ║
             ╚══════════════════════════════════════════════════════╝ -->
        <div class="tc-card">


            <!-- ┌──────────────────────────────────────────────┐
                 │  LEFT PANEL — Contact Details, Map & Social  │
                 └──────────────────────────────────────────────┘ -->
            <aside class="tc-info" aria-label="Contact information">
                <div class="tc-info__inner">

                    <!-- Panel header -->
                    <header class="tc-info__header">
                        <span class="tc-info__kicker">Liên hệ với chúng tôi</span>
                        <h1 class="tc-info__title" id="tc-heading">Hỗ trợ khách hàng</h1>
                        <p class="tc-info__sub">
                            Chúng tôi phục vụ&nbsp;7&nbsp;ngày trong tuần.<br>
                            Phản hồi trong vòng&nbsp;24&nbsp;giờ.
                        </p>
                    </header>

                    <!-- Contact detail rows -->
                    <ul class="tc-info__list">

                        <li class="tc-info__item">
                            <span class="tc-info__icon" aria-hidden="true">
                                <!-- Location pin -->
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                            </span>
                            <div class="tc-info__body">
                                <span class="tc-info__label">Địa chỉ showroom</span>
                                <span class="tc-info__value">123 Nguyễn Văn Linh, Quận 7<br>TP. Hồ Chí Minh</span>
                                <span class="tc-info__note">Mở cửa hằng ngày 8:00 – 21:00</span>
                            </div>
                        </li>

                        <li class="tc-info__item">
                            <span class="tc-info__icon" aria-hidden="true">
                                <!-- Phone handset -->
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.09 8.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14v2.92z"/>
                                </svg>
                            </span>
                            <div class="tc-info__body">
                                <span class="tc-info__label">Hotline hỗ trợ</span>
                                <a href="tel:19009999" class="tc-info__value tc-info__link">1900 9999</a>
                                <span class="tc-info__note">Thứ 2 – Chủ nhật &nbsp;·&nbsp; 8:00 – 22:00</span>
                            </div>
                        </li>

                        <li class="tc-info__item">
                            <span class="tc-info__icon" aria-hidden="true">
                                <!-- Envelope -->
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                            <div class="tc-info__body">
                                <span class="tc-info__label">Email</span>
                                <a href="mailto:support@vqstore.vn" class="tc-info__value tc-info__link">support@vqstore.vn</a>
                                <span class="tc-info__note">Phản hồi trong vòng 24 giờ</span>
                            </div>
                        </li>

                        <li class="tc-info__item">
                            <span class="tc-info__icon" aria-hidden="true">
                                <!-- Clock -->
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </span>
                            <div class="tc-info__body">
                                <span class="tc-info__label">Giờ làm việc</span>
                                <span class="tc-info__value">Thứ 2 – Thứ 7 &nbsp;·&nbsp; 8:00 – 21:00</span>
                                <span class="tc-info__note">Chủ nhật: 9:00 – 18:00</span>
                            </div>
                        </li>

                    </ul><!-- /tc-info__list -->

                    <!-- Social media links (monochrome → brand color on hover) -->
                    <div class="tc-social">
                        <p class="tc-social__label">Theo dõi chúng tôi</p>
                        <div class="tc-social__row">

                            <a href="#" class="tc-social__icon tc-social--fb" aria-label="Facebook" rel="noopener">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>

                            <a href="#" class="tc-social__icon tc-social--yt" aria-label="YouTube" rel="noopener">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M23.495 6.205a3.007 3.007 0 00-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 00.527 6.205a31.247 31.247 0 00-.522 5.805 31.247 31.247 0 00.522 5.783 3.007 3.007 0 002.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 002.088-2.088 31.247 31.247 0 00.5-5.783 31.247 31.247 0 00-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/>
                                </svg>
                            </a>

                            <a href="#" class="tc-social__icon tc-social--tt" aria-label="TikTok" rel="noopener">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                </svg>
                            </a>

                            <a href="#" class="tc-social__icon tc-social--ig" aria-label="Instagram" rel="noopener">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                </svg>
                            </a>

                        </div>
                    </div>

                </div><!-- /tc-info__inner -->
            </aside>
            <!-- /tc-info -->


            <!-- ┌──────────────────────────────────────────────┐
                 │  RIGHT PANEL — Contact Form                  │
                 └──────────────────────────────────────────────┘ -->
            <div class="tc-form-panel">

                <!-- ── Success state ── -->
                <?php if (!empty($sent)): ?>

                <div class="tc-alert tc-alert--success" role="alert">
                    <span class="tc-alert__icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </span>
                    <div>
                        <strong>Gửi thành công!</strong>
                        <p>Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi trong vòng 24 giờ.</p>
                    </div>
                </div>

                <?php else: ?>

                <!-- ── Error banner ── -->
                <?php if (!empty($error)): ?>
                <div class="tc-alert tc-alert--error" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true" style="flex-shrink:0;">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <!-- ── Form header ── -->
                <header class="tc-form__header">
                    <h2 class="tc-form__title">Gửi tin nhắn cho chúng tôi</h2>
                    <p class="tc-form__sub">Điền thông tin bên dưới, chúng tôi sẽ phản hồi trong vòng 24&nbsp;giờ.</p>
                </header>

                <!-- ── Contact form ── -->
                <form method="POST"
                      action="<?= SITE_URL ?>/contact"
                      class="tc-form"
                      novalidate>

                    <!-- Row 1: Full Name + Phone Number -->
                    <div class="tc-form__row">

                        <div class="tc-field">
                            <label class="tc-label" for="cf-name">
                                Họ và tên
                                <span class="tc-required" aria-label="bắt buộc">*</span>
                            </label>
                            <input
                                id="cf-name"
                                type="text"
                                name="name"
                                class="tc-input"
                                placeholder="Nguyễn Văn A"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                autocomplete="name"
                                required>
                        </div>

                        <div class="tc-field">
                            <label class="tc-label" for="cf-phone">
                                Số điện thoại
                                <span class="tc-required" aria-label="bắt buộc">*</span>
                            </label>
                            <input
                                id="cf-phone"
                                type="tel"
                                name="phone"
                                class="tc-input"
                                placeholder="0912 345 678"
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                autocomplete="tel"
                                required>
                        </div>

                    </div>

                    <!-- Row 2: Email + Order ID -->
                    <div class="tc-form__row">

                        <div class="tc-field">
                            <label class="tc-label" for="cf-email">
                                Địa chỉ Email
                                <span class="tc-required" aria-label="bắt buộc">*</span>
                            </label>
                            <input
                                id="cf-email"
                                type="email"
                                name="email"
                                class="tc-input"
                                placeholder="email@example.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                autocomplete="email"
                                required>
                        </div>

                        <div class="tc-field">
                            <label class="tc-label" for="cf-order">
                                Mã đơn hàng
                                <span class="tc-optional">(Tùy chọn)</span>
                            </label>
                            <input
                                id="cf-order"
                                type="text"
                                name="order_id"
                                class="tc-input"
                                placeholder="TS-2024-00000"
                                value="<?= htmlspecialchars($_POST['order_id'] ?? '') ?>">
                        </div>

                    </div>

                    <!-- Subject — custom-styled select -->
                    <div class="tc-field">
                        <label class="tc-label" for="cf-subject">Chủ đề</label>
                        <div class="tc-select-wrap">
                            <select id="cf-subject" name="subject" class="tc-input tc-select">
                                <option value="" <?= empty($_POST['subject']) ? 'selected' : '' ?>>
                                    -- Chọn chủ đề --
                                </option>
                                <option value="technical-support"
                                    <?= ($_POST['subject'] ?? '') === 'technical-support'  ? 'selected' : '' ?>>
                                    Hỗ trợ kỹ thuật
                                </option>
                                <option value="buying-advice"
                                    <?= ($_POST['subject'] ?? '') === 'buying-advice'      ? 'selected' : '' ?>>
                                    Tư vấn mua hàng
                                </option>
                                <option value="warranty-check"
                                    <?= ($_POST['subject'] ?? '') === 'warranty-check'     ? 'selected' : '' ?>>
                                    Kiểm tra bảo hành
                                </option>
                                <option value="others"
                                    <?= ($_POST['subject'] ?? '') === 'others'             ? 'selected' : '' ?>>
                                    Khác
                                </option>
                            </select>
                            <!-- Custom dropdown chevron -->
                            <svg class="tc-select-wrap__arrow"
                                 width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="tc-field">
                        <label class="tc-label" for="cf-message">
                            Nội dung tin nhắn
                            <span class="tc-required" aria-label="bắt buộc">*</span>
                        </label>
                        <textarea
                            id="cf-message"
                            name="message"
                            class="tc-input tc-textarea"
                            placeholder="Mô tả chi tiết vấn đề hoặc câu hỏi của bạn…"
                            rows="6"
                            required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="tc-submit">
                        <svg class="tc-submit__icon"
                             width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        Gửi tin nhắn
                    </button>

                </form>

                <?php endif; ?>

            </div><!-- /tc-form-panel -->

        </div><!-- /tc-card -->

    </div><!-- /tc-container -->
</section>

<?php include ROOT_PATH . '/views/layouts/footer.php'; ?>
