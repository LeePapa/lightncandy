<?php

require_once('src/lightncandy.php');

$tmpdir = sys_get_temp_dir();

class MustacheSpecTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider jsonSpecProvider
     */
    public function testSpecs($spec)
    {
        global $tmpdir;

        if (preg_match('/lambdas\\.json/', $spec['file'])) {
            $this->markTestIncomplete("Skip [{$spec['file']}.{$spec['name']}]#{$spec['no']} , lightncandy do not support this now.");
        }

        if (preg_match('/Partial/', $spec['name'])) {
            $this->markTestIncomplete("Skip [{$spec['file']}.{$spec['name']}]#{$spec['no']} , lightncandy do not support this now.");
        }

        // clean up old partials
        foreach (glob("$tmpdir/*.tmpl") as $file) {
            unlink($file);
        }

        if (isset($spec['partials'])) {
            foreach ($spec['partials'] as $name => $cnt) {
                file_put_contents("$tmpdir/$name.tmpl", $cnt);
            }
        }

        $php = LightnCandy::compile($spec['template'], Array(
            'flags' => LightnCandy::FLAG_MUSTACHE | LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_RUNTIMEPARTIAL,
            'helpers' => array(
            ),
            'basedir' => $tmpdir,
        ));
        $renderer = LightnCandy::prepare($php);

        $this->assertEquals($spec['expected'], $renderer($spec['data']), "[{$spec['file']}.{$spec['name']}]#{$spec['no']}:{$spec['desc']} PHP CODE: $php");
    }

    public function jsonSpecProvider()
    {
        $ret = Array();

        foreach (glob('spec/specs/*.json') as $file) {
           $i=0;
           $json = json_decode(file_get_contents($file), true);
           $ret = array_merge($ret, array_map(function ($d) use ($file, &$i) {
               $d['file'] = $file;
               $d['no'] = ++$i;
               return Array($d);
           }, $json['tests']));
        }

        return $ret;
    }
}


?>
