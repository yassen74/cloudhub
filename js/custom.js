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

  var $navbar = $(".navbar");
  if ($navbar.length) {
    var ticking = false;
    var syncNavbar = function() {
      $navbar.css("background-color", $(window).scrollTop() >= 600 ? "#225470" : "transparent");
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
