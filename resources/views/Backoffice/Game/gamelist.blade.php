@extends('Layouts.backoffice')

@section('contents')
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Game Setting</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a class="text-muted "
                                    href="{{ route('admin.game.index', ['game' => $game_code]) }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">
                                {{ $game_code }}
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="col-3">
                    <div class="text-center mb-n5">
                        <img src="{{ asset('back/dist') }}/images/breadcrumb/ChatBc.png" alt=""
                            class="img-fluid mb-n4" />
                    </div>
                </div>
            </div>
        </div>
        @if (Auth::user()->valid_until)
            <?php
            $humanReadableDate = date('Y-m-d H:i:s', Auth::user()->valid_until);
            ?>
            <div class="col-9" style=" padding: 1rem; ">
                <h5 class="fw-semibold mb-8">หมดอายุ : {{ $humanReadableDate }}</h5>
            </div>
        @endif
    </div>

    <div class="card">
        <form method="post" action="{{ route('admin.game.credit') }}">
            {{ csrf_field() }}
            <div class="card-body p-4">
                <h5 class="card-title fw-semibold">Credit</h5>

                <span class="d-block mb-1">Amount</span>
                <div class="input-group rounded">

                    <input type="text" class="form-control  border-end-0" aria-label="Text input with dropdown button"
                        value="{{ Auth::user()->credit }}" name="credit">
                    <button class="btn p-0 border-top border-bottom border-end border-0" type="submit">
                        <span class=" btn btn-primary m-1 rounded">ปรับเครดิต<i class="ti ti-arrow-right fs-4"></i>
                        </span>
                    </button>

                </div>
                @if ($game_code == 'pg')
                    <?php
                    $href = env('PG_URL') . '/' . 0 . '/index.html?language=th&bet_type=1&operator_token=' . $operator->operator_token . '&operator_player_session=' . Auth::user()->token . '&or=' . env('PG_API_URL');
                    ?>
                    <button class="btn p-0 border-top border-bottom border-end border-0" type="button"
                        onclick="location.href='{{ $href }}'" style="margin-top: 1rem">
                        <span class=" btn btn-primary m-1 rounded">เข้าลอบบี้<i class="ti ti-arrow-right fs-4"></i>
                        </span>
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Row -->
    <div class="row">
        @foreach ($games as $game)
            <!-- column -->
            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card">
                    @if ($game['game_image'])
                        <img class="card-img-top img-responsive" src="{{ asset($game['game_image']) }}"
                            alt="{{ $game['game_name'] }}" />
                    @else
                        <img class="card-img-top img-responsive" src="/back/images/GameIcon/{{ $game['game_code'] }}.webp"
                            alt="{{ $game['game_name'] }}" />
                    @endif
                    <?php
                    $add = '';
                    if ($game['game_type'] == 'PG') {
                        $href = env('PG_URL') . '/' . $game['game_code'] . '/index.html?language=th&bet_type=1&operator_token=' . $operator->operator_token . '&operator_player_session=' . Auth::user()->token . '&or=' . env('PG_API_URL');
                    } elseif ($game['game_type'] == 'nlm') {
                        $href = env('NLM_URL') . '/loader/game-loader.html?operator=' . $operator->operator_token . '&currency=THB&game=' . $game['game_code'] . '&device=mobile&language=th&token=' . Auth::user()->token;
                        $add = env('NLM_DATA_URL') . '/free-play/' . $game['game_code'];
                    }
                    ?>
                    <div class="card-body">
                        <h4 class="card-title">{{ $game['game_name'] }}</h4>
                        <a href="javascript:void(0)" class="btn btn-primary btn-setting"
                            onClick="setGame({{ $game['id'] }})">ตั้งค่า</a>
                        <a target="_blank" href="{{ $href }}" class="btn btn-primary">เล่น</a>
                        @if ($add)
                            <a target="_blank" href="{{ $add }}" class="btn btn-primary">เพิ่ม data</a>
                        @endif
                    </div>
                </div>
                <!-- Card -->
            </div>
            <!-- column -->
        @endforeach
    </div>
    <!-- Modal -->
    <div class="modal fade modal-lg" id="addContactModal" tabindex="-1" role="dialog"
        aria-labelledby="addContactModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title">ตั้งค่าเกมส์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" method="POST" action="{{ route('admin.game.save') }}" novalidate>
                        {{ csrf_field() }}
                        <div class="row" id="form-game-setting">
                            <div class="card">
                                <div class="card-body">
                                    <h5>ตั้งค่า</h5>
                                    <div class="mb-3 row">
                                        <label for="example-color-input" class="col-md-6 col-form-label">ลูกค้าเล่นไม่แตก
                                            (<span id="txt-range1">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="range1" name="range1"
                                                value="10" max="1000">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="example-color-input" class="col-md-6 col-form-label">ลูกค้าแตกแบบขาดทุน
                                            (<span id="txt-range2">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="range2" name="range2"
                                                value="10" max="1000">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="example-color-input"
                                            class="col-md-6 col-form-label">ลูกค้าแตกแบบได้กำไร
                                            (<span id="txt-range3">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="range3" name="range3"
                                                value="10" max="1000">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="min" class="col-md-12 col-form-label">กำไรต่ำสุด -
                                            สูงสุด (สปินนอก)</label>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="0" id="min"
                                                min="0" name="min">
                                        </div>
                                        <div class="col-md-2" style="text-align: center">
                                            <label>-</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="100" id="max"
                                                min="0" name="max">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="example-color-input" class="col-md-6 col-form-label">โอกาสได้ฟรีสปิน
                                            (<span id="txt-range4">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="range4" value="100"
                                                max="1000" name="range4">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="min" class="col-md-12 col-form-label">กำไรฟรีสปินต่ำสุด -
                                            สูงสุด (เข้าเอง, จำนวนเท่าจาก bet)</label>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="0" id="free_min"
                                                min="0" name="free_min">
                                        </div>
                                        <div class="col-md-2" style="text-align: center">
                                            <label>-</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="100" id="free_max"
                                                min="0" name="free_max">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="min" class="col-md-12 col-form-label">กำไรซื้อฟรีสปินต่ำสุด -
                                            สูงสุด (ซื้อสปิน, จำนวนเท่าจาก bet)</label>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="0" id="buy_free_min"
                                                min="0" name="buy_free_min">
                                        </div>
                                        <div class="col-md-2" style="text-align: center">
                                            <label>-</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="100" id="buy_free_max"
                                                min="0" name="buy_free_max">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card" style="background: black">
                                <div class="card-body">
                                    <h5>ตั้งค่าสปินเพิ่มเติม</h5>
                                    <div class="mb-3 row">
                                        <label for="example-color-input" class="col-md-6 col-form-label">ค่า bet เพิ่มเติม
                                            (ใส่ 0 หากไม่ต้องการใช้)</label>
                                        <div class="col-md-12">
                                            <input type="number" class="form-control" id="ex_val" name="ex_val"
                                                value="10" max="1000">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="example-color-input" class="col-md-6 col-form-label">ลูกค้าเล่นไม่แตก
                                            (<span id="txt-ex_range1">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="ex_range1" name="ex_range1"
                                                value="10" max="1000">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="example-color-input"
                                            class="col-md-6 col-form-label">ลูกค้าแตกแบบขาดทุน (<span
                                                id="txt-ex_range2">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="ex_range2" name="ex_range2"
                                                value="10" max="1000">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="example-color-input"
                                            class="col-md-6 col-form-label">ลูกค้าแตกแบบได้กำไร
                                            (<span id="txt-ex_range3">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="ex_range3" name="ex_range3"
                                                value="10" max="1000">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="min" class="col-md-12 col-form-label">กำไรลูกค้าต่ำสุด -
                                            สูงสุด (สปินนอก)</label>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="0" id="ex_min"
                                                min="0" name="ex_min">
                                        </div>
                                        <div class="col-md-2" style="text-align: center">
                                            <label>-</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="100" id="ex_max"
                                                min="0" name="ex_max">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="example-color-input" class="col-md-6 col-form-label">โอกาสได้ฟรีสปิน
                                            (<span id="txt-ex_range4">0.1%</span>)</label>
                                        <div class="col-md-12">
                                            <input type="range" class="form-range" id="ex_range4" value="100"
                                                max="1000" name="ex_range4">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="min" class="col-md-12 col-form-label">กำไรฟรีสปินต่ำสุด -
                                            สูงสุด (เข้าเอง, จำนวนเท่าจาก bet)</label>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="0" id="ex_free_min"
                                                min="0" name="ex_free_min">
                                        </div>
                                        <div class="col-md-2" style="text-align: center">
                                            <label>-</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="100" id="ex_free_max"
                                                min="0" name="ex_free_max">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="min" class="col-md-12 col-form-label">กำไรซื้อฟรีสปินต่ำสุด -
                                            สูงสุด (ซื้อสปิน, จำนวนเท่าจาก bet)</label>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="0"
                                                id="ex_buy_free_min" min="0" name="ex_buy_free_min">
                                        </div>
                                        <div class="col-md-2" style="text-align: center">
                                            <label>-</label>
                                        </div>
                                        <div class="col-md-5">
                                            <input class="form-control" type="number" value="100"
                                                id="ex_buy_free_max" min="0" name="ex_buy_free_max">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="game_id" id="game_id">
                        <input type="hidden" name="save_type" id="save_type" value="1">
                        <button class="btn btn-info mt-3 rounded-pill px-4" type="submit" onclick="saveSetting(1)">
                            บันทึก
                        </button>
                        <button class="btn btn-info mt-3 rounded-pill px-4" type="submit" onclick="saveSetting(2)">
                            บันทึกทั้งหมด
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal"> Discard </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let settings = {};
        @if (count($games))
            @foreach ($games as $game)
                settings['game-{{ $game['id'] }}'] = ({!! isset($game_s[$game['id']]) ? $game_s[$game['id']]->game_user_setting : $game['game_setting'] !!});
            @endforeach
        @endif

        function setGame(id) {
            $('#game_id').val(id);
            let keys = Object.keys(settings['game-' + id]);
            for (let i = 0; i < keys.length; i++) {
                $('#' + keys[i]).val(settings['game-' + id][keys[i]]);
                console.log('keys[i].search', keys[i].search('rang'));
                if (keys[i].search('rang') > -1) {
                    $('#txt-' + keys[i]).text((settings['game-' + id][keys[i]] / 10).toFixed(2) + '%');
                }
            }
            $('#ex_val').val(settings['game-' + id]['ex']['value']);
            keys = Object.keys(settings['game-' + id]['ex']);
            for (let i = 0; i < keys.length; i++) {
                $('#ex_' + keys[i]).val(settings['game-' + id]['ex'][keys[i]]);
                console.log('keys[i].search', keys[i].search('rang'));
                if (keys[i].search('rang') > -1) {
                    $('#txt-ex_' + keys[i]).text((settings['game-' + id]['ex'][keys[i]] / 10).toFixed(2) + '%');
                }
            }
        }

        function saveSetting(value) {
            $('#save_type').val(value);
        }
        $("#range1").on("input", function() {
            $('#txt-range1').text(($(this).val() / 10).toFixed(2) + '%');
        });
        $("#range2").on("input", function() {
            $('#txt-range2').text(($(this).val() / 10).toFixed(2) + '%');
        });
        $("#range3").on("input", function() {
            $('#txt-range3').text(($(this).val() / 10).toFixed(2) + '%');
        });
        $("#range4").on("input", function() {
            $('#txt-range4').text(($(this).val() / 10).toFixed(2) + '%');
        });
        $("#ex_range1").on("input", function() {
            $('#txt-ex_range1').text(($(this).val() / 10).toFixed(2) + '%');
        });
        $("#ex_range2").on("input", function() {
            $('#txt-ex_range2').text(($(this).val() / 10).toFixed(2) + '%');
        });
        $("#ex_range3").on("input", function() {
            $('#txt-ex_range3').text(($(this).val() / 10).toFixed(2) + '%');
        });
        $("#ex_range4").on("input", function() {
            $('#txt-ex_range4').text(($(this).val() / 10).toFixed(2) + '%');
        });
        console.log('settings', settings);
        console.log('settings json', JSON.stringify(settings));
    </script>
    <script src="{{ asset('back/dist') }}/libs/prismjs/prism.js"></script>
    <script src="{{ asset('back/dist') }}/js/apps/game-list.js"></script>
@endsection
