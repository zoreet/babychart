var bbc = {



    name: null,
    bday: null,
    days: [],
    dayTemplate: {
        bottlesPerDay: 8,
        weight: 0,
        records: []
    },
    recordTemplate: {
        time: "", // hh:mm
        pumped: 0,
        breastFed: 0,
        fed: 0,
        formula: 0,
        pipi: false,
        caca: false
    },


    init: function(name, bday) {
        bbc.name = name;
        bbc.bday = bday;
        bbc.loadData();

        $('#add-day').click(function(e) {
            bbc.addDay();
        })
    },


    model: null,
    initTemplate: function(days) {
        bbc.model = new bbc.Model(days);
        ko.applyBindings(bbc.model);
    },
    Model: function(data) {
        var self = this;
        self.days = ko.observableArray([]);

        var daysSinceBirth = moment().diff(moment(bbc.bday, 'YYYYMMDD'), 'days') + 1;
        var daysNotLogged = daysSinceBirth - data.length

        $.each(data, function(i) {
            self.days.push(new bbc.Day(this, i + daysNotLogged));
        });

        if (daysNotLogged == 1) {
            // if it's more then one day maybe you stopped keeping track
            // imagine you want to look at the data in one month's time and I add 30 empty days
            // still if the user skips one day, she can still add days from the button in the header
            var dt = JSON.parse(JSON.stringify(bbc.dayTemplate))
            self.days.unshift(new bbc.Day(dt, 0));
        }

    },
    Day: function(data, dayNo) {
        var self = this;


        self.title = moment().subtract(dayNo, 'days').format('Do MMMM');


        if (data.weight) {
            self.weight = ko.observable(data.weight);
        } else {
            if (bbc.model && bbc.model.days().length) {
                self.weight = ko.observable(bbc.model.days()[0].weight());
            } else {
                self.weight = ko.observable(1);
            }
        }


        var prevBottlesPerDay = 0;
        if (bbc.model && bbc.model.days().length)
            prevBottlesPerDay = bbc.model.days()[0].bottlesPerDay();
        self.bottlesPerDay = ko.observable(prevBottlesPerDay ? prevBottlesPerDay : data.bottlesPerDay);




        // RECORDS
        self.records = ko.observableArray();
        self.addRecord = function() {
            rt = JSON.parse(JSON.stringify(bbc.recordTemplate));

            // we add the time after we finish feeding her
            // and normally that takes 20 minutes
            // to make it easier to read I'm going to round it down to the nearest 15 minutes
            var time = moment().subtract('20', 'minutes');
            minutes = 15 * Math.floor(time.minute() / 15);
            time.minute(minutes);
            rt.time = time.format('HH:mm');

            self.records.push(new bbc.Record(rt));
            if (bbc.model && bbc.model.days().length) { // when we initialize the first day, this is empty, so there's nothing to save
                bbc.saveData();
            }
        }

        if (!data.records.length) { // have one record in by default, to make it easier to start loggin the food
            self.addRecord();
        } else {
            $.each(data.records, function() {
                self.records.push(new bbc.Record(this));
            });
        }


        self.totalFed = ko.computed(function() {
            var total = 0;
            $.each(self.records(), function() {
                total +=
                    parseInt(this.breastFed()) +
                    parseInt(this.formula()) +
                    parseInt(this.fed());
            });
            return total;
        });


        self.feedGoal = ko.computed(function() {
            return parseInt(self.weight()) / 1000 * 150
        })


    },
    Record: function(data) {
        var self = this;
        self.time = data.time;
        self.pumped = ko.observable(data.pumped);
        self.fed = ko.observable(data.fed);
        self.breastFed = ko.observable(data.breastFed);
        self.formula = ko.observable(data.formula);
        self.pipi = ko.observable(data.pipi);
        self.caca = ko.observable(data.caca);
    },



    loadData: function(id) {
        $.post(
            "./loadData.php", {
                id: bbc.name
            },
            function(data) {
                data = $.parseJSON(data)
                if (data.code == 200) {
                    var days = $.parseJSON(data.result);
                    if (bbc.validateData(days)) {
                        bbc.initTemplate(days);
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
                id: bbc.name,
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
                !data[day].hasOwnProperty('weight') ||
                !data[day].hasOwnProperty('records')
            ) return -4

            if (data[day].records.constructor !== Array)
                return -5
            if (data[day].records.length) {
                for (record in data[day].records)
                    if (!data[day].records[record].hasOwnProperty('time') ||
                        !data[day].records[record].hasOwnProperty('pumped') ||
                        !data[day].records[record].hasOwnProperty('breastFed') ||
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



    addDay: function() {
        data = JSON.parse(JSON.stringify(bbc.dayTemplate));

        var daysSinceBirth = moment().diff(moment(bbc.bday, 'YYYYMMDD'), 'days');
        var daysNotLogged = daysSinceBirth - bbc.model.days().length;
        bbc.model.days.unshift(new bbc.Day(data, daysNotLogged));

        bbc.saveData();
    }



}