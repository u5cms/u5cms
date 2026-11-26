<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/provision-policy.inc.php';
mt_srand(261126); $checks=0;
for($case=0;$case<1500;$case++) {
 $n=mt_rand(1,12);$edges=[];$reach=[];
 for($i=0;$i<$n;$i++) {$edges[$i]=[];for($j=0;$j<$n;$j++) {$on=mt_rand(0,4)===0;$reach[$i][$j]=$i===$j||$on;if($on)$edges[$i][]=$j;}}
 for($k=0;$k<$n;$k++)for($i=0;$i<$n;$i++)for($j=0;$j<$n;$j++)$reach[$i][$j]=$reach[$i][$j]||($reach[$i][$k]&&$reach[$k][$j]);
 [$groups,$membership]=u5ProvisionComponents($edges);
 for($i=0;$i<$n;$i++)for($j=0;$j<$n;$j++) {if(($membership[$i]===$membership[$j])!==($reach[$i][$j]&&$reach[$j][$i]))throw new RuntimeException('SCC closure differs');$checks++;}
 foreach($edges as $node=>$parents)foreach($parents as $parent)if($membership[$node]!==$membership[$parent]&&$membership[$node]>$membership[$parent])throw new RuntimeException('Permission dependency order differs');
}
$edges=[];for($i=0;$i<30000;$i++)$edges[$i]=$i<29999?[$i+1]:[];
[$groups,$membership]=u5ProvisionComponents($edges);if(count($groups)!==30000)throw new RuntimeException('Long dependency chain lost');
echo json_encode(['status'=>'PASS','random_graphs'=>1500,'reachability_comparisons'=>$checks,'long_chain_nodes'=>30000])."\n";
