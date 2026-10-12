document.addEventListener("DOMContentLoaded", function () {
    const popup = document.getElementById('myCookiePopup');
    const header = document.getElementById('toggleCookieBody');

    if (!popup || !header) return;

    // --- CÁC HÀM HỖ TRỢ XỬ LÝ COOKIE ---
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i].trim();
            if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }

    // Các thành phần checkbox
    const chkAnalytics = document.getElementById('cookieAnalytics');
    const chkMarketing = document.getElementById('cookieMarketing');

    // --- KIỂM TRA & HIỂN THỊ TRẠNG THÁI CŨ ---
    // Khôi phục trạng thái nút gạt (nếu người dùng đã từng lưu tuỳ chọn)
    const savedPrefs = getCookie("philo_cookie_prefs");
    if (savedPrefs) {
        try {
            const prefs = JSON.parse(savedPrefs);
            if (chkAnalytics) chkAnalytics.checked = prefs.analytics;
            if (chkMarketing) chkMarketing.checked = prefs.marketing;
        } catch (e) {
            console.error("Lỗi đọc cookie preferences", e);
        }
    }

    // Kiểm tra xem đã từng xác nhận chưa, nếu rồi thì ẩn popup đi
    const cookieConsent = getCookie("philo_cookie_consent");
    if (cookieConsent) {
        popup.style.display = 'none';
        return; 
    }

    // --- XỬ LÝ HIỆU ỨNG THU NHỎ/PHÓNG TO ---
    let isAnimating = false;
    header.addEventListener('click', () => {
        if (isAnimating) return;
        isAnimating = true;

        popup.classList.add('slide-down');
        setTimeout(() => {
            popup.classList.toggle('minimized');
            setTimeout(() => {
                popup.classList.remove('slide-down');
                isAnimating = false;
            }, 50);
        }, 350);
    });

    // --- XỬ LÝ CÁC NÚT HÀNH ĐỘNG ---
    const btnReject = document.getElementById('btnCookieReject');
    const btnSave = document.getElementById('btnCookieSave');
    const btnAcceptAll = document.getElementById('btnCookieAcceptAll');

    function closePopupWithAnimation() {
        popup.classList.add('slide-down');
        setTimeout(() => {
            popup.style.display = 'none';
        }, 400);
    }

    // 1. Nút Từ chối (Tắt hết các tùy chọn)
    if (btnReject) {
        btnReject.addEventListener('click', function () {
            if (chkAnalytics) chkAnalytics.checked = false;
            if (chkMarketing) chkMarketing.checked = false;
            
            setCookie("philo_cookie_consent", "rejected", 30);
            setCookie("philo_cookie_prefs", JSON.stringify({ analytics: false, marketing: false }), 30);
            closePopupWithAnimation();
        });
    }

    // 2. Nút Lưu tùy chọn (Lưu theo trạng thái gạt thực tế)
    if (btnSave) {
        btnSave.addEventListener('click', function () {
            const currentPrefs = {
                analytics: chkAnalytics ? chkAnalytics.checked : false,
                marketing: chkMarketing ? chkMarketing.checked : false
            };
            
            setCookie("philo_cookie_consent", "custom", 365);
            setCookie("philo_cookie_prefs", JSON.stringify(currentPrefs), 365);
            closePopupWithAnimation();
        });
    }

    // 3. Nút Đồng ý tất cả (Bật hết các tùy chọn)
    if (btnAcceptAll) {
        btnAcceptAll.addEventListener('click', function () {
            if (chkAnalytics) chkAnalytics.checked = true;
            if (chkMarketing) chkMarketing.checked = true;
            
            setCookie("philo_cookie_consent", "accepted", 365);
            setCookie("philo_cookie_prefs", JSON.stringify({ analytics: true, marketing: true }), 365);
            closePopupWithAnimation();
        });
    }
});