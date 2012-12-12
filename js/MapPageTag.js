$(function() {
        var availableTags = [
            "Absinthe House",
            "Walrus Saloon",
            "Rio Grande Boulder",
            "The Catacombs",
            "Sundown Saloon",
            "Pearl Street Pub",
            "The Attic Bar and Bistro",
            "Tahona Tequila Bistro",
            "Press Play",
            "Lazy Dog Sports Bar and Grill"
        ];
        $( "#tags" ).autocomplete({
            source: availableTags
        });
    });