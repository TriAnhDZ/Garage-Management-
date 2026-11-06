const primaryColor = '#4834d4'
const warningColor = '#f0932b'
const successColor = '#6ab04c'
const dangerColor = '#eb4d4b'

const themeCookieName = 'theme'
const themeDark = 'dark'
const themeLight = 'light'

const body = document.getElementsByTagName('body')[0]

function setCookie(cname, cvalue, exdays) {
  var d = new Date()
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000))
  var expires = "expires="+d.toUTCString()
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/"
}

function getCookie(cname) {
  var name = cname + "="
  var ca = document.cookie.split(';')
  for(var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1)
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length)
    }
  }
  return ""
}

loadTheme()

function loadTheme() {
	var theme = getCookie(themeCookieName)
	body.classList.add(theme === "" ? themeLight : theme)
}

function switchTheme() {
	if (body.classList.contains(themeLight)) {
		body.classList.remove(themeLight)
		body.classList.add(themeDark)
		setCookie(themeCookieName, themeDark)
	} else {
		body.classList.remove(themeDark)
		body.classList.add(themeLight)
		setCookie(themeCookieName, themeLight)
	}
}

const sidebarCookieName = 'sidebar_state'

function loadSidebarState() {
    if (window.innerWidth <= 768) {
        body.classList.remove('sidebar-expand')
        return
    }
    var savedState = getCookie(sidebarCookieName)
    if (savedState === 'collapsed') {
        body.classList.remove('sidebar-expand')
    } else {
        body.classList.add('sidebar-expand')
    }
}

loadSidebarState()

function collapseSidebar() {
    body.classList.toggle('sidebar-expand')
    var isExpanded = body.classList.contains('sidebar-expand')
    if (window.innerWidth > 768) {
        setCookie(sidebarCookieName, isExpanded ? 'expanded' : 'collapsed', 365)
        try {
            localStorage.setItem('sidebar_state', isExpanded ? 'expanded' : 'collapsed')
        } catch(e) {}
    }
}

window.onclick = function(event) {
	openCloseDropdown(event)
}

function closeAllDropdown() {
	var dropdowns = document.getElementsByClassName('dropdown-expand')
	for (var i = 0; i < dropdowns.length; i++) {
		dropdowns[i].classList.remove('dropdown-expand')
	}
}

function openCloseDropdown(event) {
	var toggleBtn = event.target.closest('.dropdown-toggle')
	if (!toggleBtn) {
		// Đóng dropdown khi click bên ngoài menu
		if (!event.target.closest('.dropdown-menu')) {
			closeAllDropdown()
		}
	} else {
		var toggle = toggleBtn.dataset.toggle
		var content = document.getElementById(toggle)
		if (content) {
			if (content.classList.contains('dropdown-expand')) {
				closeAllDropdown()
			} else {
				closeAllDropdown()
				content.classList.add('dropdown-expand')
			}
		}
	}
}

var ctx = document.getElementById('myChart')
if (ctx && typeof Chart !== 'undefined') {
	ctx.height = 500
	ctx.width = 500
	var data = {
		labels: ['January', 'February', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		datasets: [{
			fill: false,
			label: 'Completed',
			borderColor: successColor,
			data: [120, 115, 130, 100, 123, 88, 99, 66, 120, 52, 59],
			borderWidth: 2,
			lineTension: 0,
		}, {
			fill: false,
			label: 'Issues',
			borderColor: dangerColor,
			data: [66, 44, 12, 48, 99, 56, 78, 23, 100, 22, 47],
			borderWidth: 2,
			lineTension: 0,
		}]
	}

	var lineChart = new Chart(ctx, {
		type: 'line',
		data: data,
		options: {
			maintainAspectRatio: false,
			bezierCurve: false,
		}
	})
}

/**
 * Xử lý trạng thái tải & Thông báo khi web đang xử lý tác vụ
 */
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tự động gắn loading spinner cho mọi nút Submit khi gửi form
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            // Kiểm tra tính hợp lệ của form theo chuẩn HTML5
            if (form.checkValidity && !form.checkValidity()) {
                return; // Nếu thiếu trường bắt buộc thì không chặn
            }
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                // Đổi nút sang trạng thái đang xử lý
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-processing');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý dữ liệu...';

                // Kích hoạt thanh tiến trình trên đầu trang
                showTopProgressBar();
            }
        });
    });

    // 2. Tự động hiển thị spinner cho các nút tác vụ xuất PDF hoặc gửi Gmail
    document.querySelectorAll('a.btn-pdf, a.btn-gmail, a[href*="downloadPdf"], a[href*="approveAndRepair"]').forEach(function(link) {
        link.addEventListener('click', function() {
            showTopProgressBar();
        });
    });

    // 3. Tự động làm mờ và đóng alert flash sau 7 giây
    const flashAlert = document.getElementById('message_container');
    if (flashAlert) {
        setTimeout(function() {
            flashAlert.style.transition = 'opacity 0.6s ease';
            flashAlert.style.opacity = '0';
            setTimeout(function() {
                if (flashAlert.parentNode) {
                    flashAlert.style.display = 'none';
                }
            }, 600);
        }, 7000);
    }
});

/**
 * Thanh tiến trình gradient chạy trên đầu trang khi web đang xử lý
 */
function showTopProgressBar() {
    let bar = document.getElementById('top-progress-bar');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'top-progress-bar';
        bar.className = 'top-progress-bar';
        document.body.appendChild(bar);
    }
    bar.style.display = 'block';
    bar.style.width = '30%';
    setTimeout(() => { bar.style.width = '70%'; }, 200);
    setTimeout(() => { bar.style.width = '95%'; }, 500);
}