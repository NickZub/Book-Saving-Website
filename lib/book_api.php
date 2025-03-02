<?php

function fetch_book($book)
{
    $data = ["query" => $book];
    $endpoint = "https://book-info-hub.p.rapidapi.com/search.php";
    $isRapidAPI = true;
    $rapidAPIHost = "book-info-hub.p.rapidapi.com";
    $result = get($endpoint, "BOOK_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    /* $result = ["status" => 200, "response" =>
    '[
        {
          "id": "15801668-the-girls-of-atomic-city",
          "title": "The Girls of Atomic City: The Untold Story of the Women Who Helped Win World War II",
          "author": "Denise Kiernan",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1352912866l/15801668._SY500_.jpg",
          "rating": "3.71"
        },
        {
          "id": "35069547-the-atomic-city-girls",
          "title": "The Atomic City Girls",
          "author": "Janet Beard",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1501532758l/35069547._SY500_.jpg",
          "rating": "3.53"
        },
        {
          "id": "53360084-atomic-love",
          "title": "Atomic Love",
          "author": "Jennie Fields",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1589121176l/53360084._SY500_.jpg",
          "rating": "3.64"
        },
        {
          "id": "58684535-atomic-anna",
          "title": "Atomic Anna",
          "author": "Rachel Barenbaum",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1643682020l/58684535._SY500_.jpg",
          "rating": "3.80"
        },
        {
          "id": "25810606-the-atomic-weight-of-love",
          "title": "The Atomic Weight of Love",
          "author": "Elizabeth J. Church",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1459102377l/25810606._SY500_.jpg",
          "rating": "3.93"
        },
        {
          "id": "25897720-the-winter-fortress",
          "title": "The Winter Fortress: The Epic Mission to Sabotage Hitlers Atomic Bomb",
          "author": "Neal Bascomb",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1443466025l/25897720._SY500_.jpg",
          "rating": "4.23"
        },
        {
          "id": "42779062-the-bastard-brigade",
          "title": "The Bastard Brigade: The True Story of the Renegade Scientists and Spies Who Sabotaged the Nazi Atomic Bomb",
          "author": "Sam Kean",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1559868522l/42779062._SY500_.jpg",
          "rating": "4.32"
        },
        {
          "id": "50452295-countdown-1945",
          "title": "Countdown 1945: The Extraordinary Story of the Atomic Bomb and the 116 Days That Changed the World (Chris Wallace’s Countdown Series)",
          "author": "Chris Wallace",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1578971705l/50452295._SY500_.jpg",
          "rating": "4.25"
        },
        {
          "id": "43431483-atomic-marriage",
          "title": "Atomic Marriage",
          "author": "Curtis Sittenfeld",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1546507643l/43431483._SY500_.jpg",
          "rating": "2.85"
        },
        {
          "id": "61453100-midcentury-cocktails",
          "title": "Midcentury Cocktails: History, Lore, and Recipes from Americas Atomic Age",
          "author": "Cecelia Tichi",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1657896164l/61453100._SY500_.jpg",
          "rating": "3.75"
        },
        {
          "id": "3474928-atomic-robo-and-the-fightin-scientists-of-tesladyne",
          "title": "Atomic Robo and the Fightin Scientists of Tesladyne (Atomic Robo, #1)",
          "author": "Brian Clevinger",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1347271973l/3474928._SY500_.jpg",
          "rating": "4.14"
        },
        {
          "id": "27753938-tokyo-ghost-vol-1",
          "title": "Tokyo Ghost, Vol. 1: Atomic Garden",
          "author": "Rick Remender",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1456502365l/27753938._SY500_.jpg",
          "rating": "4.02"
        },
        {
          "id": "23172739-mission-atomic",
          "title": "Mission Atomic (The 39 Clues: Doublecross, #4)",
          "author": "Sarwat Chadda",
          "image": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1468287017l/23172739._SY500_.jpg",
          "rating": "4.28"
        }
      ]',
    ]; */
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }

    //Because id column already exists as default, change the id from api to bookID
    if (isset($result)) {
        foreach($result as &$book){
            $book["bookID"] = $book["id"];
            unset($book["id"]);
        }
        unset($book);
    }
    return $result;
}