app.controller('mainCtrl', function ($scope, DummyImages) {


    DummyImages.getImages().success(function(data, status, headers, config) {
        console.log(data);
    });



    // $scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
    //     salvattore.rescanMediaQueries();
    // });



});