<?php

namespace App\Http\Controllers\Api;

use App\Models\Coupon;
use App\Models\CouponHistory;
use App\Models\SeamlessSetting;
use App\Models\User;
use App\Models\WebContent;
use App\Services\RedisService;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LineApiController extends Controller
{
    protected $mainUrl;
    protected $mainStaticUrl;
    protected $redisService;
    protected $channel_access;
    function __construct(RedisService $redisService)
    {
        $this->channel_access = env("LINE_CHANNEL_ACCESS");
    }


    public function quickRandom($length = 16)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    public function replyText($replyToken, $data)
    {
        try {
            //            $data = json_encode($data);
            $client = new Client([
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer  ' . $this->channel_access]
            ]);
            $data = '{
                "replyToken":"' . $replyToken . '",
                "messages":[
                    {
                        "type":"text",
                        "text":"' . $data . '"
                    }
                ]
            }';
            $response = $client->post(
                'https://api.line.me/v2/bot/message/reply',
                ['body' => $data]
            );
            $response = json_decode($response->getBody(), true);
            return $response;
        } catch (\Exception $exception) {
        }
    }

    function getBubbleUser($user)
    {
        $user->login_token = $this->quickRandom(20);
        $user->save();

        $position_text = array(
            1 => 'DEMO',
            2 => 'SEAMLESS',
            3 => 'WALLET',
            4 => 'SELLER',
            5 => 'ADMIN'
        );
        $thaiHumanDate = 'ถาวร';
        if ($user->valid_until) {
            $thaiHumanDate = strftime('%d %B %Y %H:%M:%S', $user->valid_until);
        }
        $timestamp = time();
        $status_text = $timestamp > $user->valid_until ? 'หมดอายุ' : 'ปกติ';

        $day_text = '';

        if ($user->position == 4) {
            $day_text = '
            {
                "type": "box",
                "layout": "horizontal",
                "contents": [
                  {
                    "type": "text",
                    "text": "วันคงเหลือ",
                    "size": "sm",
                    "color": "#555555",
                    "flex": 0
                  },
                  {
                    "type": "text",
                    "text": "' . $user->day_sell . ' วัน",
                    "size": "sm",
                    "color": "#111111",
                    "align": "end"
                  }
                ]
              },';
        }
        $text = '{
            "type": "bubble",
            "body": {
              "type": "box",
              "layout": "vertical",
              "contents": [
                {
                  "type": "text",
                  "text": "PG DEMO SYSTEM",
                  "weight": "bold",
                  "color": "#1DB446",
                  "size": "sm"
                },
                {
                  "type": "text",
                  "text": "' . $user->username . '",
                  "weight": "bold",
                  "size": "xxl",
                  "margin": "md"
                },
                {
                  "type": "text",
                  "text": "ใช้ได้ถึง : ' . $thaiHumanDate . '",
                  "size": "xs",
                  "color": "#aaaaaa",
                  "wrap": true
                },
                {
                  "type": "separator",
                  "margin": "xxl"
                },
                {
                  "type": "box",
                  "layout": "vertical",
                  "margin": "xxl",
                  "spacing": "sm",
                  "contents": [
                    {
                      "type": "box",
                      "layout": "horizontal",
                      "contents": [
                        {
                          "type": "text",
                          "text": "ประเภท",
                          "size": "sm",
                          "color": "#555555",
                          "flex": 0
                        },
                        {
                          "type": "text",
                          "text": "' . $position_text[$user->position] . '",
                          "size": "sm",
                          "color": "#111111",
                          "align": "end"
                        }
                      ]
                    },'.$day_text.'
                    {
                      "type": "box",
                      "layout": "horizontal",
                      "contents": [
                        {
                          "type": "text",
                          "text": "API",
                          "size": "sm",
                          "color": "#555555",
                          "flex": 0
                        },
                        {
                          "type": "text",
                          "text": "' . ($user->position == 1 ? 'No' : 'Yes') . '",
                          "size": "sm",
                          "color": "#111111",
                          "align": "end"
                        }
                      ]
                    },
                    {
                      "type": "box",
                      "layout": "horizontal",
                      "contents": [
                        {
                          "type": "text",
                          "text": "ค่าใช้จ่าย",
                          "size": "sm",
                          "color": "#555555",
                          "flex": 0
                        },
                        {
                          "type": "text",
                          "text": "0",
                          "size": "sm",
                          "color": "#111111",
                          "align": "end"
                        }
                      ]
                    },
                    {
                      "type": "separator",
                      "margin": "xxl"
                    },
                    {
                      "type": "box",
                      "layout": "horizontal",
                      "margin": "xxl",
                      "contents": [
                        {
                          "type": "text",
                          "text": "ยอดเงิน",
                          "size": "sm",
                          "color": "#555555"
                        },
                        {
                          "type": "text",
                          "text": "' . number_format($user->credit, 2) . '",
                          "size": "sm",
                          "color": "#111111",
                          "align": "end"
                        }
                      ]
                    },
                    {
                      "type": "box",
                      "layout": "horizontal",
                      "contents": [
                        {
                          "type": "text",
                          "text": "สถานะ",
                          "size": "sm",
                          "color": "#555555"
                        },
                        {
                          "type": "text",
                          "text": "' . $status_text . '",
                          "size": "sm",
                          "color": "#111111",
                          "align": "end"
                        }
                      ]
                    },
                    {
                      "type": "box",
                      "layout": "horizontal",
                      "contents": [
                        {
                          "type": "text",
                          "text": "ชื่อผู้ใช้",
                          "size": "sm",
                          "color": "#555555"
                        },
                        {
                          "type": "text",
                          "text": "' . $user->username . '",
                          "size": "sm",
                          "color": "#111111",
                          "align": "end"
                        }
                      ]
                    },
                    {
                      "type": "box",
                      "layout": "horizontal",
                      "contents": [
                        {
                          "type": "text",
                          "text": "รหัสผ่าน",
                          "size": "sm",
                          "color": "#555555"
                        },
                        {
                          "type": "text",
                          "text": "' . $user->plain_password . '",
                          "size": "sm",
                          "color": "#111111",
                          "align": "end"
                        }
                      ]
                    }
                  ]
                },
                {
                  "type": "separator",
                  "margin": "xxl"
                },
                {
                  "type": "box",
                  "layout": "horizontal",
                  "margin": "md",
                  "contents": [
                    {
                      "type": "text",
                      "text": "TRANSACTION ID",
                      "size": "xs",
                      "color": "#aaaaaa",
                      "flex": 0
                    },
                    {
                      "type": "text",
                      "text": "#TEST",
                      "color": "#aaaaaa",
                      "size": "xs",
                      "align": "end"
                    }
                  ]
                }
            ],
            "action": {
                "type": "uri",
                "label": "action",
                "uri": "https://onsen168.com"
              }
            },
            "styles": {
              "footer": {
                "separator": true
              }
            }
          }';

        return $text;
    }

    public function replyFlex($replyToken, $data, $header = 'ข้อมูลผู้ใช้')
    {
        try {
            //            $data = json_encode($data);
            $client = new Client([
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer  ' . $this->channel_access]
            ]);
            $data = '{
    "replyToken": "' . $replyToken . '",
    "messages":[{
                    "type": "flex",
                    "altText": "' . $header . '",
                    "contents": ' . $data . '
                }]}';
            $response = $client->post(
                'https://api.line.me/v2/bot/message/reply',
                ['body' => $data]
            );
            $response = json_decode($response->getBody(), true);
            return $response;
        } catch (\Exception $exception) {

        }
    }

    public function lineWebhook(Request $request)
    {
        //        return response()->json(['msg' => 'success'], 200);
        $data = $request->all();
        try {
            if ($request->input('events')) {
                $data = ($request->input('events'));
                $replyToken = $data[0]['replyToken'];
                $msg = $data[0]['message']['text'];
                //                dd($replyToken);
                $msg_arr = explode(':', str_replace(' ', '', $msg));
                $line_id = $data[0]['source']['userId'];
                $timestamp = time();
                if ($msg_arr[0] == 'ข้อมูลผู้ใช้') {
                    $user = User::where('line_id', $line_id)->first();
                    if (!$user) {
                        $user = new User();
                        $password = $this->quickRandom(6);
                        $user->username = $this->quickRandom(5);
                        $user->name = $user->username;
                        $user->password = bcrypt($password);
                        $user->credit = 10000;
                        $user->line_id = $line_id;
                        $user->position = 1;
                        $user->plain_password = $password;
                        $user->token = $this->quickRandom(4) . '-' . $this->quickRandom(4) . '-' . $this->quickRandom(4) . '-' . $this->quickRandom(4);
                        $user->valid_until = $timestamp + (30 * 60);
                        $user->save();
                        $user->username = 'U' . $user->id . $user->username;
                        $user->save();
                        $seamless = new SeamlessSetting();
                        $seamless->user_id = $user->id;
                        $seamless->operator_token = 'T' . $user->id . '-' . $this->quickRandom(4) . '-' . $this->quickRandom(4) . '-' . $this->quickRandom(4) . '-' . $this->quickRandom(4);
                        $seamless->secret_key = $user->username . '_' . $this->quickRandom(5);
                        $seamless->setting_url = 'https://pg.foxi-bet.com/web-api/callback/PGSOFT2';
                        $seamless->save();
                    } else {

                        if ($timestamp > $user->valid_until && $user->valid_until) {
                            // Get the current date in the format 'Y-m-d'
                            $currentDate = date('Y-m-d');

                            // Get the date part of the timestamp in the same format
                            $timestampDate = date('Y-m-d', $user->valid_until);

                            // Check if the timestamp's date is equal to the current date
                            $isToday = ($timestampDate == $currentDate);

                            // Output the result
                            if (!$isToday) {
                                $user->valid_until = $timestamp + (30 * 60);
                                $user->save();
                            }
                        }
                    }

                    $this->replyFlex($replyToken, $this->getBubbleUser($user));
                } else if ($msg_arr[0] == 'คูปอง') {
                    $user = User::where('line_id', $line_id)->first();
                    if ($user && $user->position == 4) {
                        $day = $msg_arr[1];
                        if (+$day <= +$user->day_sell) {
                            $user->day_sell -= $day;
                            $user->save();
                            $coupon = new Coupon();
                            $coupon->created_by = $user->id;
                            $coupon->coupon_code = 'CPN-'.$user->id.'-'.$this->quickRandom(20);
                            $coupon->day_add = +$day;
                            $coupon->save();
                            $this->replyText($replyToken, 'เติม:'.$coupon->coupon_code);
                            $this->replyText($replyToken, 'รหัสคูปองสำหรับ ' . $day . ' วัน');
                        } else {
                            $this->replyText($replyToken, 'จำนวนวันคงเหลือไม่เพียงพอ');
                        }
                    }
                } else if ($msg_arr[0] == 'เติม') {
                    $user = User::where('line_id', $line_id)->first();
                    if ($user && $user->valid_until) {
                        $coupon = $msg_arr[1];
                        $cpn = Coupon::where('coupon_code', $coupon)->first();
                        if ($cpn && $cpn->is_expired == 0) {
                            $now = time();
                            $cpn->is_expired = 1;
                            $cpn->save();
                            $cpn_history = new CouponHistory();
                            $cpn_history->used_by = $user->id;
                            if ($user->valid_until >= $now) {
                                $cpn_history->date_start = $user->valid_until;
                            } else {
                                $cpn_history->date_start = $now;
                            }
                            $cpn_history->date_after = $cpn_history->date_start + ((60 * 60 * 24) * $cpn->day_add);
                            $cpn_history->save();
                            $user->valid_until = $cpn_history->date_after;
                            $user->save();
                            $this->replyText($replyToken, 'เติมเวลาจำนวน ' .$cpn->day_add. ' วันเรียบร้อย');
                        } else {
                            $this->replyText($replyToken, 'รหัสคูปองไม่ถูกต้อง');
                        }
                    } else if ($user && $user->valid_until) {
                        $this->replyText($replyToken, 'เติมคูปองไม่สำเร็จ');
                    }
                }
            }
        } catch (\Exception $e) {

        }
        return response()->json(['msg' => 'success'], 200);
    }
}
