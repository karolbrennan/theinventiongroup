    </div>
</section>

<footer>
    <div class="row">
        <div class="small-12 medium-4 columns">
            <a href="/"><img src="/assets/img/newlogo.svg" alt="The Invention Group"></a>
        </div>
        <div class="small-12 medium-8 columns">
            <?php require("nav.php"); ?>
        </div>
    </div>
    <div class="row">
        <div class="small-12 columns">
            <p><span>&copy; <?php echo date('Y'); ?> The Invention Group</span>
                <a href="/privacy">Privacy Policy</a> | <a href="/refunds">Refund Policy</a> | <a href="/disclaimer">Disclaimer</a></p>
        </div>
    </div>
</footer>


<script src='https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="/assets/js/jquery.hideseek.min.js"></script>

<script>

    $(document).ready(function() {
        $('#faqsearch').hideseek({
            list:           '.faqlist',
            nodata:         'Sorry, no results found.',
            attribute:      'text',
            highlight:      true
        });
    });

    $(document).ready(function(){
        $('#menutoggle').click(function(){
            $('nav .menu').toggleClass('active')
        });
    });

    $(window).scroll(function() {
        var windscroll = $(window).scrollTop();

        if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
            if (windscroll >= 10) {
                $('header').addClass('fixed');
                $('#content').addClass('fixedheader');
            } else {
                $('header').removeClass('fixed');
                $('#content').removeClass('fixedheader');
            }
        }
    }).scroll();
</script>

    </body>
</html>
