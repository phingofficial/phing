<?php
/**
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/Task.php';

/**
 * AstroTask
 * Calculates the geocentric apparent longitude in degrees of each planet (+ sun)
 * and rounds to the nearest integer.
 *
 * If any of the planets align, the build will fail with an error message.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 * @link    https://github.com/lhartikk/AstroBuild
 * @link    http://www.stjarnhimlen.se/comp/ppcomp.html
 */
class AstroTask extends Task
{
    /** @var string[] $planetNames */
    private $planetNames = array('Sun', 'Mercury', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune');

    /**
     * Main method to calculate the planet alignments.
     *
     * {@inheritdoc}
     */
    public function main()
    {
        $now = new DateTime('now', new DateTimeZone('UCT'));
        list($year, $month, $day, $hour) = explode(':', $now->format('y:m:d:H'));

        $d = $this->calculateDay($year, $month, $day, $hour);
        $alignments = array();

        foreach ($this->planetNames as $name) {
            $alignments[$name] = round($this->calcGeocentricAlignments($name, $d));
        }

        $this->log(var_export($alignments, true), Project::MSG_DEBUG);
        $this->checkAlignments($alignments);
    }

    /**
     * Calculate day for the time scale used.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     *
     * @return int
     */
    protected function calculateDay($year, $month, $day, $hour)
    {
        return 367 * $year - 7 * (int)(($year + (int)(($month + 9) / 12)) / 4) + (int)(275 * $month / 9) + $day - 730530 + (int)((float)$hour / 24.0);
    }

    /**
     * Calculate geocentric alignments of the planet.
     *
     * @param string $planetName
     *
     * @param int $d
     *
     * @return float|int
     */
    protected function calcGeocentricAlignments($planetName, $d)
    {
        $sun = $this->calcOrbitalElements('Sun', $d);
        $planet = $this->calcOrbitalElements($planetName, $d);
        $lonsun = $sun['v'] + $sun['w'];
        $xs = $sun['r'] * cos($lonsun);
        $ys = $sun['r'] * sin($lonsun);
        $xh = $planet['xh'];
        $yh = $planet['yh'];
        $xg = $xh + $xs;
        $yg = $yh + $ys;
        $helio_degree = rad2deg(atan2($xh, $yh));
        $geo_degree = rad2deg(atan2($xg, $yg));
        $helio_degree = 90 - $helio_degree;
        $geo_degree = 90 - $geo_degree;
        if ($helio_degree < 0) {
            $helio_degree += 360;
        }
        if ($geo_degree < 0) {
            $geo_degree += 360;
        }
        return $geo_degree;
    }

    /**
     * Check alignments of the planets.
     *
     * @param array $alignments
     *
     * @return bool
     *
     * @throws BuildException
     */
    protected function checkAlignments($alignments)
    {
        $uniques = array_unique($alignments);
        $dups = array_diff_assoc($alignments, $uniques);
        $list = array_intersect($alignments, $dups);

        $grouped = array();
        while (list($planet, $degree) = each($list)) {
            $grouped["$degree"][] = $planet;
        }

        if (count($grouped) === 0) {
            $this->log('NO PLANETS ALIGNED', Project::MSG_INFO);

            return true;
        }
        /** @var FailTask $fail */
        $fail = $this->project->createTask('fail');

        foreach ($grouped as $degree => $planets) {
            if (count($planets) > 1) {
                $fail->addText("\n");
                $fail->addText('PLANETS ALIGNED: ' . implode(', ', $planets) . "\n");
                $fail->addText('ALIGNMENT: ' . $degree . ' degrees' . "\n");
            }
        }

        $fail->main();

        return false;
    }

    /**
     * Calculate orbital elements
     *
     * @param string $planet_name
     * @param int $d
     *
     * @return array
     */
    protected function calcOrbitalElements($planet_name, $d)
    {
        $planet = $this->getPlanet($planet_name, $d);
        $N = $planet['N'];
        $i = $planet['i'];
        $w = $planet['w'];
        $a = $planet['a'];
        $e = $planet['e'];
        $M = $planet['M'];
        $E = $M + $e * sin($M) * (1.0 + $e * cos($M));
        $xv = $a * (cos($E) - $e);
        $yv = $a * (sqrt(1.0 - $e * $e) * sin($E));
        $v = atan2($yv, $xv);
        $r = sqrt($xv * $xv + $yv * $yv);
        $xh = $r * (cos($N) * cos($v + $w) - sin($N) * sin($v + $w) * cos($i));
        $yh = $r * (sin($N) * cos($v + $w) + cos($N) * sin($v + $w) * cos($i));
        $zh = $r * (sin($v + $w) * sin($i));
        $lonecl = atan2($yh, $xh);
        $latecl = atan2($zh, sqrt($xh * $xh + $yh * $yh));
        return array(
            'r' => $r,
            'v' => $v,
            'xh' => $xh,
            'yh' => $yh,
            'w' => $w,
            'lonecl' => $lonecl,
            'latecl' => $latecl
        );
    }

    /**
     * Get planet.
     *
     * @param string $name
     * @param int $d
     *
     * @return array
     */
    protected function getPlanet($name, $d)
    {
        if ($name === 'Sun') {
            return array(
                'N' => deg2rad(0.0),
                'i' => deg2rad(0.0),
                'w' => deg2rad(282.9404 + 4.70935E-5 * $d),
                'a' => 1.000000,
                'e' => 0.016709 - 1.151E-9 * $d,
                'M' => deg2rad(356.0470 + 0.9856002585 * $d)
            );
        } elseif ($name === 'Mercury') {
            return array(
                'N' => deg2rad(48.3313 + 3.24587E-5 * $d),
                'i' => deg2rad(7.0047 + 5.00E-8 * $d),
                'w' => deg2rad(29.1241 + 1.01444E-5 * $d),
                'a' => 0.387098,
                'e' => 0.205635 + 5.59E-10 * $d,
                'M' => deg2rad(168.6562 + 4.0923344368 * $d)
            );
        } elseif ($name === 'Venus') {
            return array(
                'N' => deg2rad(76.6799 + 2.46590E-5 * $d),
                'i' => deg2rad(3.3946 + 2.75E-8 * $d),
                'w' => deg2rad(54.8910 + 1.38374E-5 * $d),
                'a' => 0.723330,
                'e' => 0.006773 - 1.302E-9 * $d,
                'M' => deg2rad(48.0052 + 1.6021302244 * $d)
            );
        } elseif ($name === 'Mars') {
            return array(
                'N' => deg2rad(49.5574 + 2.11081E-5 * $d),
                'i' => deg2rad(1.8497 - 1.78E-8 * $d),
                'w' => deg2rad(286.5016 + 2.92961E-5 * $d),
                'a' => 1.523688,
                'e' => 0.093405 + 2.516E-9 * $d,
                'M' => deg2rad(18.6021 + 0.5240207766 * $d)
            );
        } elseif ($name === 'Jupiter') {
            return array(
                'N' => deg2rad(100.4542 + 2.76854E-5 * $d),
                'i' => deg2rad(1.3030 - 1.557E-7 * $d),
                'w' => deg2rad(273.8777 + 1.64505E-5 * $d),
                'a' => 5.20256,
                'e' => 0.048498 + 4.469E-9 * $d,
                'M' => deg2rad(19.8950 + 0.0830853001 * $d)
            );
        } elseif ($name === 'Saturn') {
            return array(
                'N' => deg2rad(113.6634 + 2.38980E-5 * $d),
                'i' => deg2rad(2.4886 - 1.081E-7 * $d),
                'w' => deg2rad(339.3939 + 2.97661E-5 * $d),
                'a' => 9.55475,
                'e' => 0.055546 - 9.499E-9 * $d,
                'M' => deg2rad(316.9670 + 0.0334442282 * $d)
            );
        } elseif ($name === 'Uranus') {
            return array(
                'N' => deg2rad(74.0005 + 1.3978E-5 * $d),
                'i' => deg2rad(0.7733 + 1.9E-8 * $d),
                'w' => deg2rad(96.6612 + 3.0565E-5 * $d),
                'a' => 19.18171 - 1.55E-8 * $d,
                'e' => 0.047318 + 7.45E-9 * $d,
                'M' => deg2rad(142.5905 + 0.011725806 * $d)
            );
        } elseif ($name === 'Neptune') {
            return array(
                'N' => deg2rad(131.7806 + 3.0173E-5 * $d),
                'i' => deg2rad(1.7700 - 2.55E-7 * $d),
                'w' => deg2rad(272.8461 - 6.027E-6 * $d),
                'a' => 30.05826 + 3.313E-8 * $d,
                'e' => 0.008606 + 2.15E-9 * $d,
                'M' => deg2rad(260.2471 + 0.005995147 * $d)
            );
        }
    }
}
