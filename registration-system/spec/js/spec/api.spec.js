
describe("Bachelor prototype behaviour", function() {
    var bachelor = new Bachelor();

    it("should return empty bachelor properties", function() {
        expect(bachelor.getProperties()).toEqual({
            'forname': null,
            'sirname': null,
            'anday':   null,
            'abday':   null,
            'antyp':   null,
            'abtyp':   null,
            'pseudo':  null,
            'mehl':    null,
            'essen':   null,
            'public':  null,
            'studityp':null,
            'comment': null
        });
    });

    it("should handle single property setting/getting", function() {
        expect(bachelor.getValue('forname')).toBe(null);

        bachelor.setValue('forname', 'Franz');
        expect(bachelor.getValue('forname')).toBe('Franz');

        expect(bachelor.getProperties()).toEqual({
            'forname': 'Franz',
            'sirname': null,
            'anday':   null,
            'abday':   null,
            'antyp':   null,
            'abtyp':   null,
            'pseudo':  null,
            'mehl':    null,
            'essen':   null,
            'public':  null,
            'studityp':null,
            'comment': null
        });

        bachelor.resetValue('forname');
        expect(bachelor.getValue('forname')).toBe(null);
    });

    it("should handle multi property setting/getting", function() {
        bachelor.setValues({
            forname: 'Franz',
            sirname: 'Hals'
        });

        expect(bachelor.getValue('forname')).toBe('Franz');
        expect(bachelor.getProperties()).toEqual({
            'forname': 'Franz',
            'sirname': 'Hals',
            'anday':   null,
            'abday':   null,
            'antyp':   null,
            'abtyp':   null,
            'pseudo':  null,
            'mehl':    null,
            'essen':   null,
            'public':  null,
            'studityp':null,
            'comment': null
        });
    });

    it("should revoke setting/getting invalid attributes", function() {
        expect(function () {
            bachelor.setValue('illegalAttribute', 'bla');
        }).toThrow();

        expect(function () {
            bachelor.resetValue('illegalAttribute')
        }).toThrow();

        expect(function () {
            bachelor.setValues({
                illegalAttribute: 'bla',
                sirname: 'blabla'
            })
        }).toThrow();

        expect(function () {
            bachelor.getValue('illegalAttribute');
        }).toThrow();
    });
});