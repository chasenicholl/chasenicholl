var Pages = {} || Pages;

Pages.Home = ( function() {
    
    var obj = function() {        
        this.__construct();
        return this;    
    };
    
    obj.prototype = {
        
        skills: [],
        constructor: obj,
        __construct: function()
        {
              
        },
        randomize: function()
        {
            this.randomizeSkills();
            this.randomizeSkillFonts();  
        },
        randomizeSkills: function()
        {
            this.skills = [];
            var spans = document.getElementById('skill_box').children;
            for (var i = 0; i < spans.length; i++) {
                this.skills.push(spans[i].innerHTML);
            }
            this.skills = this.shuffleArray(this.skills);
        },
        randomizeSkillFonts: function()
        {
            var skills = '';
            for (var i = 0; i < this.skills.length; i++) {
                var font_size = Math.floor(Math.random() * 72) + 12;
                skills += ' <span style="font-size:'+font_size+'px;">';
                skills += this.skills[i];
                skills += '</span> ';
            }
            
            //$J('#skill_box').fadeOut( 250, function() {
                document.getElementById('skill_box').innerHTML = skills;
                //$J('#skill_box').fadeIn(250);    
            //} );
        },
        
        shuffleArray: function(o)
        {
            for(var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
            return o;
        }
    
    };
    
    return obj;
    
} ) ();