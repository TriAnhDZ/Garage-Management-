    <!-- Import Scripts (Có thuộc tính SRI & Crossorigin bảo vệ CWE-345) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2g3xmGVOgkSITXDnik75ZfyoCoUM88x/0M46g0VnO8A89kX=" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="assets/index.js"></script>
    <script>
        // Tự động đóng thông báo flash sau 4 giây
        const flashMsg = document.getElementById("message_container");
        if (flashMsg) {
            setTimeout(function() {
                flashMsg.style.transition = "opacity 0.5s ease";
                flashMsg.style.opacity = "0";
                setTimeout(function() { flashMsg.style.display = "none"; }, 500);
            }, 4000);
        }
    </script>
</body>
</html>

