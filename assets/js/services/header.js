app.service('DummyImages', function($http) {
    return {
        getImages: function () {
            var url = "";
            return $http({
                method: 'GET',
                url: url
            });
        }
    };
});

