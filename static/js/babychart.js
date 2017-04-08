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
        breastFed: 0,
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


    model: null,
    initTemplate: function() {
        bbc.model = new bbc.Model(bbc.days);
        ko.applyBindings(bbc.model);
    },
    Model: function(data) {
        var self = this;
        self.days = ko.observableArray([]);
        $.each(data, function() {
            self.days.push(new bbc.Day(this));
        })
    },
    Day: function(data) {
        var self = this;

        self.addRecord = function() {
            rt = JSON.parse(JSON.stringify(bbc.recordTemplate));

            // we add the time after we finish feeding her
            // and normally that takes 20 minutes
            // to make it easier to read I'm going to round to the nearest 15 minutes
            var time = moment().subtract('20', 'minutes');
            minutes = 15 * Math.round(time.minute() / 15);
            time.minute(minutes);
            rt.time = time.format('HH:mm');

            self.records.push(new bbc.Record(rt));
            if (bbc.model.days().length) { // when we initialize the first day, this is empty
                bbc.saveData();
            }
        }

        self.records = ko.observableArray();
        if (!data.records.length) {
            self.addRecord();
        }
        // self.root.addRecord();
        $.each(data.records, function() {
            self.records.push(new bbc.Record(this));
        })

        if( data.weight ) {
            self.weight = ko.observable( data.weight );
        } else {
            if( bbc.model.days().length ) {
                self.weight = ko.observable( bbc.model.days()[0].weight() );
            } else {
                self.weight = ko.observable( 1 );
            }
        }


        if (bbc.model && bbc.model.days().length) {
            prevDate = bbc.model.days()[0].date;
            self.date = moment(prevDate, 'YYYYMMDD').add(1, 'days');
        } else {
            self.date = moment(data.date, 'YYYYMMDD');
        }
        self.title = moment(self.date, 'YYYYMMDD').format('Do MMMM');


        var prevBottlesPerDay = 0;
        if (bbc.model && bbc.model.days().length)
            prevBottlesPerDay = bbc.model.days()[0].bottlesPerDay();
        self.bottlesPerDay = ko.observable(prevBottlesPerDay ? prevBottlesPerDay : data.bottlesPerDay);

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



    addDay: function(index) {
        if (!index) {
            index = 0;
        }

        data = JSON.parse(JSON.stringify(bbc.dayTemplate))

        if (!index) {
            bbc.model.days.unshift(new bbc.Day(data));
        }
        bbc.saveData();
    },
    removeDay: function(index) {},
    showDay: function(index) {},



    addRecord: function(index, day) {},
    updateRecord: function(index, day) {},
    removeRecord: function(index, day) {},
    showRecord: function(index, day) {},



}