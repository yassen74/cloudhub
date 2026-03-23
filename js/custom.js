$(document).ready(function() {
  var $cards = $(".card");
  if ($cards.length) {
    $cards.hover(
      function() {
        $(this)
          .addClass("shadow")
          .css("cursor", "pointer");
      },
      function() {
        $(this).removeClass("shadow");
      }
    );
  }

  var $navbar = $(".navbar.ch-navbar");
  var hasHomeHero = $(".home-hero").length > 0;
  if ($navbar.length && hasHomeHero) {
    var ticking = false;
    var syncNavbar = function() {
      $navbar.css("background-color", $(window).scrollTop() >= 80 ? "rgba(7, 17, 29, 0.92)" : "rgba(7, 17, 29, 0.52)");
      ticking = false;
    };

    syncNavbar();
    $(window).on("scroll", function() {
      if (ticking) {
        return;
      }

      ticking = true;
      if (window.requestAnimationFrame) {
        window.requestAnimationFrame(syncNavbar);
      } else {
        setTimeout(syncNavbar, 16);
      }
    });
  } else if ($navbar.length) {
    $navbar.css("background-color", "rgba(7, 17, 29, 0.92)");
  }

  var $playlistItems = $("#playlist li");
  var $videoArea = $("#videoarea");
  if ($playlistItems.length && $videoArea.length) {
    $playlistItems.on("click", function() {
      var movieUrl = $(this).attr("movieurl");
      if (movieUrl) {
        $videoArea.attr({ src: movieUrl });
      }
    });

    var firstMovieUrl = $playlistItems.eq(0).attr("movieurl");
    if (firstMovieUrl) {
      $videoArea.attr({ src: firstMovieUrl });
    }
  }
});
