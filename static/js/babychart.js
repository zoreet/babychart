var bbc = {



    id: null,
    days: [],
    currentDay: 0, //index of days
    dayTemplate: {
        date: '20170322',
        bottlesPerDay: 8,
        weight: 0,
        records: []
    },
    recordTemplate: {
        time: "13:22", // hh:mm
        pumped: 0,
        fed: 0,
        formula: 0,
        pipi: false,
        caca: false
    },


    init: function(id) {
        bbc.id = id;
        bbc.loadData();

        $('#add-day').click(function(e) {
            bbc.addDay();
        })
    },



    initTemplate: function() {
        bbc.model = new bbc.Model(bbc.days);
        ko.applyBindings(bbc.model);
    },
    model: null,
    Day: function(data) {
        var self = this;
        self.records = ko.observableArray(data.records);
        self.weight = data.weight;

        if (bbc.model && bbc.model.days().length) {
            prevDate = bbc.model.days()[0].date;
            self.date = moment(prevDate, 'YYYYMMDD').add(1, 'days');
        } else {
            self.date = moment(data.date, 'YYYYMMDD').format('YYYYMMDD');;
        }

        self.title = moment(self.date, 'YYYYMMDD').format('Do MMMM');


        var prevBottlesPerDay = 0;
        if (bbc.model && bbc.model.days().length)
            prevBottlesPerDay = bbc.model.days()[0].bottlesPerDay();
        self.bottlesPerDay = ko.observable(prevBottlesPerDay ? prevBottlesPerDay : data.bottlesPerDay);

        self.addRecord = function(e) {
            rt = (function() {
                return bbc.recordTemplate;
            })();

            // we add the time after we finish feeding her
            // and normally that takes 20 minutes
            // to make it easier to read I'm going to round to the nearest 15 minutes
            var time = moment().subtract('20', 'minutes');
            minutes = 15 * Math.round(time.minute() / 15);
            time.minute(minutes);
            rt.time = time.format('HH:mm');

            self.records.push(bbc.recordTemplate);
            bbc.saveData()
        }
        self.removeRecord = function() {
            var response = window.confirm('Are you sure you want to remove this record?');
            if (response) {
                self.records.remove(this);
                bbc.saveData();
            }
        }
    },
    Record: function(data) {
        var self = this;
        self.time = data.time;
        self.pumped = ko.observable(data.pumped);
        self.fed = ko.observable(data.fed);
        self.formula = ko.observable(data.formula);
        self.pipi = ko.observable(data.pipi);
        self.caca = ko.observable(data.caca);
    },
    Model: function(data) {
        var self = this;
        self.saveData = function(e) {}
        self.days = ko.observableArray($.map(bbc.days, function(day) {
            return new bbc.Day(day);
        }));
    },



    loadData: function(id) {
        $.post(
            "./loadData.php", {
                id: bbc.id
            },
            function(data) {
                data = $.parseJSON(data)
                if (data.code == 200) {
                    var days = $.parseJSON(data.result);
                    if (bbc.validateData(days)) {
                        bbc.days = days;
                        bbc.initTemplate();
                    } else {
                        alert("The data on the server looks broken. I won't load it so nothing breaks further");
                        console.log(data, days);
                    }
                } else {
                    alert("I couldn't get the data from the server :(");
                    console.error(data);
                }
            }
        );
    },

    saveData: function() {
        var data = bbc.model.days();
        var code = bbc.validateData(data, true, true);
        if (code < 0) {
            alert("The data is invalid and I can't save it. Error " + code);
            console.log(data)
            return;
        }

        $.post(
            "./saveData.php", {
                id: bbc.id,
                data: ko.toJSON(bbc.model.days())
            },
            function(data) {
                console.log("Response when saving", data)
            }
        );
    },

    validateData: function(data, notEmpty, isKO) {
        if (isKO) {
            data = JSON.parse(ko.toJSON(data));
        }
        // the data has to be an array
        if (data.constructor !== Array)
            return -1;

        // if the array is empty
        if (!data.length) {
            if (notEmpty) {
                return -2
            }
            return 1
        }

        // first level is good. moving to the day level

        for (day in data) {
            // if there are no days or day is not an object
            if (data[day].constructor !== Object)
                return -3

            if ( //one of the following is missing
                !data[day].hasOwnProperty('bottlesPerDay') ||
                !data[day].hasOwnProperty('date') ||
                !data[day].hasOwnProperty('records')
                // !data[day].hasOwnProperty('weight') // optional
            ) return -4

            if (data[day].records.constructor !== Array)
                return -5
            if (data[day].records.length) {
                for (record in data[day].records)
                    if (!data[day].records[record].hasOwnProperty('time') ||
                        !data[day].records[record].hasOwnProperty('pumped') ||
                        !data[day].records[record].hasOwnProperty('fed') ||
                        !data[day].records[record].hasOwnProperty('formula') ||
                        !data[day].records[record].hasOwnProperty('pipi') ||
                        !data[day].records[record].hasOwnProperty('caca')
                    )
                        return -6
            }
        }

        // if nothing failed, then we're good
        return 1;
    },



    addDay: function(index) {
        if (!index) {
            index = 0;
        }
        var dt = (function() {
            return bbc.dayTemplate
        })();

        console.log(dt);


        if (index) {
            dt.title = index + 1
        } else {
            dt.title = bbc.model.days().length + 1;
        }
        bbc.model.days.unshift(new bbc.Day(dt));
        bbc.saveData();
    },
    removeDay: function(index) {},
    showDay: function(index) {},



    addRecord: function(index, day) {},
    updateRecord: function(index, day) {},
    removeRecord: function(index, day) {},
    showRecord: function(index, day) {},



}