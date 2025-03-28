<?php
/**
 * Plugin Name: Korean Loan Interest Calculator
 * Description: 대출이자 계산기 - 원리금균등상환 및 원금균등상환 거치기간 지원
 * Version: 1.0
 * Author: Created with Claude
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class KoreanLoanCalculator {
    
    public function __construct() {
        // Register shortcode
        add_shortcode('loan_calculator', array($this, 'render_loan_calculator'));
    }
    
    public function render_loan_calculator() {
        ob_start();
        ?>
        <!-- 초간단 대출이자 계산기 -->
        <div class="loan-calculator" style="max-width: 100%; margin: 20px auto; font-family: sans-serif; background: linear-gradient(145deg,#e8dbc2,#f5f1e6); border-radius: 10px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.15),inset 0 1px 1px rgba(255,255,255,0.6); border: 1px solid #d6c191;">
            <!-- 계산기 제목 -->
            <div style="text-align: center; margin-bottom: 15px;">
                <div style="font-size: 24px; font-weight: 600; color: #8b6914; text-shadow: 0 1px 1px rgba(255,255,255,0.5);">대출이자 계산기 💰</div>
            </div>
            
            <!-- 상환방식 선택 -->
            <div style="background: linear-gradient(to bottom,#fff,#f9f6f0); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #d6c191; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                <div style="margin-bottom: 10px; font-weight: bold; color: #8b6914;">💼 상환방식</div>
                
                <div id="repayment-methods-container" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <!-- 만기일시상환 -->
                    <div class="repayment-method" style="flex: 1; min-width: 120px; padding: 5px;">
                        <label class="method-label" style="display: block; padding: 15px; background: linear-gradient(145deg,#D4AF37,#B8860B); border-radius: 8px; text-align: center; cursor: pointer; color: white;">
                            <input type="radio" name="loan-type" value="bullet" checked style="margin-right: 5px;" onclick="toggleGracePeriod()">
                            <div style="font-size: 24px; margin-bottom: 5px; text-shadow: -1px -1px 0px rgba(0, 0, 0, 0.3), 1px 1px 0px rgba(255, 255, 255, 0.3);">💰</div>
                            <div style="font-weight: bold;">만기일시상환</div>
                        </label>
                    </div>
                    
                    <!-- 원리금균등상환 -->
                    <div class="repayment-method" style="flex: 1; min-width: 120px; padding: 5px;">
                        <label class="method-label" style="display: block; padding: 15px; background: linear-gradient(145deg,#e8dbc2,#f5f1e6); border-radius: 8px; text-align: center; cursor: pointer; color: #8b6914;">
                            <input type="radio" name="loan-type" value="equal-payment" style="margin-right: 5px;" onclick="toggleGracePeriod()">
                            <div style="font-size: 24px; margin-bottom: 5px; text-shadow: -1px -1px 0px rgba(0, 0, 0, 0.1), 1px 1px 0px rgba(255, 255, 255, 0.5);">⚖️</div>
                            <div style="font-weight: bold;">원리금균등상환</div>
                        </label>
                    </div>
                    
                    <!-- 원금균등상환 -->
                    <div class="repayment-method" style="flex: 1; min-width: 120px; padding: 5px;">
                        <label class="method-label" style="display: block; padding: 15px; background: linear-gradient(145deg,#e8dbc2,#f5f1e6); border-radius: 8px; text-align: center; cursor: pointer; color: #8b6914;">
                            <input type="radio" name="loan-type" value="equal-principal" style="margin-right: 5px;" onclick="toggleGracePeriod()">
                            <div style="font-size: 24px; margin-bottom: 5px; text-shadow: -1px -1px 0px rgba(0, 0, 0, 0.1), 1px 1px 0px rgba(255, 255, 255, 0.5);">📊</div>
                            <div style="font-weight: bold;">원금균등상환</div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- 입력 필드 -->
            <div style="background: linear-gradient(to bottom,#fff,#f9f6f0); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #d6c191; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #8b6914;">💳 대출금액</label>
                    <div style="position: relative;">
                        <input id="loan-amount" style="width: 100%; padding: 10px; font-size: 16px; border: 1px solid #d6c191; border-radius: 5px; box-sizing: border-box; background: #fffcf5; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);" type="text" value="5,000" placeholder="대출금액 입력" oninput="formatAmountInput(this)">
                        <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #8b6914; font-weight: bold;">만원</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #8b6914;">📊 연이자율</label>
                    <div style="position: relative;">
                        <input id="interest-rate" style="width: 100%; padding: 10px; font-size: 16px; border: 1px solid #d6c191; border-radius: 5px; box-sizing: border-box; background: #fffcf5; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);" type="number" value="3.5" step="0.1" min="0" placeholder="연 이자율 입력">
                        <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #8b6914; font-weight: bold;">%</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #8b6914;">📅 대출기간</label>
                    <div style="position: relative;">
                        <input id="loan-term" style="width: 100%; padding: 10px; font-size: 16px; border: 1px solid #d6c191; border-radius: 5px; box-sizing: border-box; background: #fffcf5; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);" type="number" value="60" min="1" placeholder="개월 수 입력">
                        <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #8b6914; font-weight: bold;">개월</span>
                    </div>
                </div>
                
                <div id="grace-period-row" style="margin-bottom: 12px; display: none;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #8b6914;">⏰ 거치기간</label>
                    <div style="position: relative;">
                        <input id="grace-period" style="width: 100%; padding: 10px; font-size: 16px; border: 1px solid #d6c191; border-radius: 5px; box-sizing: border-box; background: #fffcf5; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);" type="number" value="0" min="0" placeholder="거치기간 입력">
                        <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #8b6914; font-weight: bold;">개월</span>
                    </div>
                </div>
            </div>
            
            <!-- 광고 영역 1 - 여백 제거 -->
            <div class="loan-calculator-ad" style="width: 100%; max-width: 100%; text-align: center; overflow: hidden; border: 1px dashed #d6c191; background: #fffaf0; padding: 10px; border-radius: 5px; box-sizing: border-box; margin-bottom: 0;">
                <div style="font-size: 12px; color: #8b6914; margin-bottom: 5px;"><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1274782176645203"
     crossorigin="anonymous"></script>
<!-- 우측 상단 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-1274782176645203"
     data-ad-slot="8934130399"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script></div>
                <!-- 광고 코드 삽입 위치 -->
                <div class="ad-content" style="max-width: 100%; overflow: hidden;"></div>
            </div>
            
            <!-- 계산하기 버튼 - 여백 제거, 폰트 크게 -->
            <div style="text-align: center; margin-top: 0;">
                <button onclick="calculateLoan()" style="width: 100%; background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; border: none; padding: 24px 20px; font-size: 24px; font-weight: bold; border-radius: 5px; cursor: pointer; box-shadow: 0 3px 6px rgba(139,105,20,0.3); text-shadow: 0 1px 1px rgba(0,0,0,0.3); position: relative; overflow: hidden; transition: all 0.3s ease;">계산하기</button>
            </div>
            
            <!-- 결과 영역 -->
            <div id="loan-result" style="margin-top: 20px;">
                <div id="result-content" style="display: none;">
                    <!-- 요약 정보 -->
                    <div style="background: linear-gradient(to bottom,#fff,#f9f6f0); padding: 15px; border-radius: 8px; border: 1px solid #d6c191; box-shadow: 0 2px 6px rgba(0,0,0,0.05); margin-bottom: 15px;">
                        <div style="margin-bottom: 10px; border-bottom: 1px solid #e0d0a0; padding-bottom: 8px;">
                            <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                                <div style="flex: 1; min-width: 120px; text-align: center; padding: 3px; margin-bottom: 3px;">
                                    <div style="font-size: 13px; color: #8b6914; margin-bottom: 2px; font-weight: bold;">상환방식</div>
                                    <div id="res-loan-type" style="font-weight: bold; color: #333;"></div>
                                </div>
                                <div style="flex: 1; min-width: 120px; text-align: center; padding: 3px; margin-bottom: 3px;">
                                    <div style="font-size: 13px; color: #8b6914; margin-bottom: 2px; font-weight: bold;">대출금액</div>
                                    <div id="res-loan-amount" style="font-weight: bold; color: #333;"></div>
                                </div>
                                <div style="flex: 1; min-width: 120px; text-align: center; padding: 3px;">
                                    <div style="font-size: 13px; color: #8b6914; margin-bottom: 2px; font-weight: bold;">이자율</div>
                                    <div id="res-loan-rate" style="font-weight: bold; color: #333;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 요약 결과 -->
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <div style="flex: 1; min-width: 150px; text-align: center; padding: 12px; border-radius: 8px; background: linear-gradient(145deg,#f0e6cf,#fdf7e8); box-shadow: inset 0 1px 1px rgba(255,255,255,0.6), 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e0d0a0;">
                                <div style="font-size: 14px; color: #8b6914; margin-bottom: 3px; font-weight: bold;">총 납입원금</div>
                                <div id="res-total-principal" style="font-size: 18px; font-weight: bold; color: #333; text-shadow: 0 1px 1px rgba(255,255,255,0.7);"></div>
                            </div>
                            <div style="flex: 1; min-width: 150px; text-align: center; padding: 12px; border-radius: 8px; background: linear-gradient(145deg,#f0e6cf,#fdf7e8); box-shadow: inset 0 1px 1px rgba(255,255,255,0.6), 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e0d0a0;">
                                <div style="font-size: 14px; color: #8b6914; margin-bottom: 3px; font-weight: bold;">총 이자</div>
                                <div id="res-total-interest" style="font-size: 18px; font-weight: bold; color: #333; text-shadow: 0 1px 1px rgba(255,255,255,0.7);"></div>
                            </div>
                            <div style="flex: 1; min-width: 150px; text-align: center; padding: 12px; border-radius: 8px; background: linear-gradient(145deg,#f0e6cf,#fdf7e8); box-shadow: inset 0 1px 1px rgba(255,255,255,0.6), 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e0d0a0;">
                                <div style="font-size: 14px; color: #8b6914; margin-bottom: 3px; font-weight: bold;">총 납입금액</div>
                                <div id="res-total-payment" style="font-size: 18px; font-weight: bold; color: #b8860b; text-shadow: 0 1px 1px rgba(255,255,255,0.7);"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- PC용 상환 일정 테이블 -->
                    <div id="pc-table" style="background: linear-gradient(to bottom,#fff,#f9f6f0); padding: 15px; border-radius: 8px; border: 1px solid #d6c191; box-shadow: 0 2px 6px rgba(0,0,0,0.05); margin-bottom: 15px; overflow-x: auto;">
                        <div style="font-size: 16px; color: #8b6914; margin-bottom: 10px; font-weight: bold; text-align: center;">상환 스케줄</div>
                        <table id="payment-table" style="width: 100%; border-collapse: collapse; min-width: 600px;">
                            <thead>
                                <tr style="background: linear-gradient(145deg,#D4AF37,#B8860B); color: white;">
                                    <th style="padding: 10px; text-align: center; border: 1px solid #d6c191;">회차</th>
                                    <th style="padding: 10px; text-align: center; border: 1px solid #d6c191;">납입원금</th>
                                    <th style="padding: 10px; text-align: center; border: 1px solid #d6c191;">이자</th>
                                    <th style="padding: 10px; text-align: center; border: 1px solid #d6c191;">납입금액</th>
                                    <th style="padding: 10px; text-align: center; border: 1px solid #d6c191;">대출잔액</th>
                                </tr>
                            </thead>
                            <tbody id="payment-schedule">
                                <!-- 결과가 여기에 동적으로 생성됨 -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 모바일용 상환 일정 카드 목록 -->
                    <div id="mobile-cards" style="display: none; margin-bottom: 15px; background: linear-gradient(to bottom,#fff,#f9f6f0); padding: 15px; border-radius: 8px; border: 1px solid #d6c191; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                        <div style="font-size: 16px; color: #8b6914; margin-bottom: 10px; font-weight: bold; text-align: center;">상환 스케줄</div>
                        <div id="mobile-payment-schedule">
                            <!-- 모바일용 결과가 여기에 동적으로 생성됨 -->
                        </div>
                        
                        <!-- 모바일 하단 합계 카드 - 수정된 레이아웃 -->
                        <div style="margin-top: 15px; padding: 15px; border-radius: 8px; background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                            <div style="font-size: 16px; font-weight: bold; margin-bottom: 10px;">납입 합계</div>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <tr>
                                    <td style="padding: 4px; text-align: left;">총 납입원금:</td>
                                    <td id="mobile-total-principal" style="padding: 4px; text-align: right; font-weight: bold;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; text-align: left;">총 이자:</td>
                                    <td id="mobile-total-interest" style="padding: 4px; text-align: right; font-weight: bold;"></td>
                                </tr>
                                <tr style="border-top: 1px solid rgba(255,255,255,0.3);">
                                    <td style="padding: 4px; padding-top: 8px; text-align: left;">총 납입금액:</td>
                                    <td id="mobile-total-payment" style="padding: 4px; padding-top: 8px; text-align: right; font-weight: bold;"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- 광고 영역 2 - 여백 제거 -->
                    <div class="loan-calculator-ad" style="width: 100%; max-width: 100%; text-align: center; overflow: hidden; border: 1px dashed #d6c191; background: #fffaf0; padding: 10px; border-radius: 5px; box-sizing: border-box; margin-bottom: 0;">
                        <div style="font-size: 12px; color: #8b6914; margin-bottom: 5px;"><script src="https://ads-partners.coupang.com/g.js"></script>
<script>
	new PartnersCoupang.G({"id":851132,"template":"carousel","trackingCode":"AF7962515","width":"100%","height":"240","tsource":""});
</script></div>
                        <!-- 광고 코드 삽입 위치 -->
                        <div class="ad-content" style="max-width: 100%; overflow: hidden;"></div>
                    </div>
                    
                    <!-- 다시 계산하기 버튼 - 여백 제거, 폰트 크게 -->
                    <div style="text-align: center; margin-top: 0;">
                        <button onclick="resetCalculator()" style="width: 100%; background: linear-gradient(145deg,#c14040,#9e2a2a); color: white; border: none; padding: 24px 20px; font-size: 24px; font-weight: bold; border-radius: 5px; cursor: pointer; box-shadow: 0 3px 6px rgba(0,0,0,0.2); text-shadow: 0 1px 1px rgba(0,0,0,0.3); position: relative; overflow: hidden; transition: all 0.3s ease;">다시 계산하기</button>
                    </div>
                </div>
            </div>
            
            <!-- 추가 CSS 스타일 -->
            <style>
                /* 모바일 환경에서 상환방식 세로 배열 및 폰트 크기 조정 */
                @media (max-width: 768px) {
                    #repayment-methods-container {
                        flex-direction: column;
                    }
                    
                    .repayment-method {
                        width: 100%;
                    }
                    
                    .method-label {
                        padding: 10px !important;
                    }
                    
                    .method-label .method-icon {
                        font-size: 20px !important;
                        margin-bottom: 3px !important;
                    }
                    
                    .method-label .method-name {
                        font-size: 14px !important;
                    }
                    
                    /* 모바일 결과값 글자 크기 조정 */
                    #mobile-payment-schedule .payment-item {
                        font-size: 13px !important;
                    }
                    
                    /* 버튼 크기 조정 */
                    button {
                        padding: 16px !important;
                        font-size: 18px !important;
                    }
                }
            </style>
            
            <!-- 인라인 JavaScript -->
            <script>
                // 광고 요소에 빛 효과 추가
                document.querySelectorAll('button').forEach(function(button) {
                    button.addEventListener('mouseenter', function() {
                        this.style.opacity = '0.9';
                        this.style.transform = 'translateY(-2px)';
                    });
                    
                    button.addEventListener('mouseleave', function() {
                        this.style.opacity = '1';
                        this.style.transform = 'translateY(0)';
                    });
                    
                    button.addEventListener('mousedown', function() {
                        this.style.transform = 'translateY(1px)';
                    });
                    
                    button.addEventListener('mouseup', function() {
                        this.style.transform = 'translateY(-2px)';
                    });
                });
                
                // 상환방식 선택에 따른 라벨 스타일 변경 및 거치기간 표시/숨김
                function toggleGracePeriod() {
                    console.log('상환방식 변경됨');
                    
                    // 모든 라벨 초기화
                    var labels = document.querySelectorAll('.method-label');
                    labels.forEach(function(label) {
                        label.style.background = 'linear-gradient(145deg,#e8dbc2,#f5f1e6)';
                        label.style.color = '#8b6914';
                    });
                    
                    // 선택된 라벨 스타일 변경
                    var selectedRadio = document.querySelector('input[name="loan-type"]:checked');
                    if (selectedRadio) {
                        selectedRadio.parentNode.style.background = 'linear-gradient(145deg,#D4AF37,#B8860B)';
                        selectedRadio.parentNode.style.color = 'white';
                        
                        // 거치기간 표시 여부
                        var gracePeriodRow = document.getElementById('grace-period-row');
                        if (selectedRadio.value === 'equal-payment' || selectedRadio.value === 'equal-principal') {
                            gracePeriodRow.style.display = 'block';
                            console.log('거치기간 필드 표시됨');
                        } else {
                            gracePeriodRow.style.display = 'none';
                            console.log('거치기간 필드 숨겨짐');
                        }
                    }
                }
                
                // 천단위 구분 함수
                function formatNumber(number) {
                    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
                
                // 대출금액 입력필드에 천단위 구분 기능 추가
                function formatAmountInput(input) {
                    input.value = input.value.replace(/[^\d]/g, '').replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
                }
                
                // 모바일 감지 함수
                function isMobile() {
                    return window.innerWidth <= 768;
                }
                
                // 화면 크기에 따른 테이블/카드 표시
                function updateDisplayMode() {
                    if (isMobile()) {
                        document.getElementById('pc-table').style.display = 'none';
                        document.getElementById('mobile-cards').style.display = 'block';
                    } else {
                        document.getElementById('pc-table').style.display = 'block';
                        document.getElementById('mobile-cards').style.display = 'none';
                    }
                }
                
                // 화면 크기 변경 시 테이블/카드 표시 업데이트
                window.addEventListener('resize', function() {
                    if (document.getElementById('result-content').style.display !== 'none') {
                        updateDisplayMode();
                    }
                });
                
                // 계산하기 버튼 클릭 처리
                function calculateLoan() {
                    try {
                        console.log('대출 계산 시작');
                        
                        // 선택된 대출 유형 찾기
                        var selectedRadio = document.querySelector('input[name="loan-type"]:checked');
                        var loanType = selectedRadio ? selectedRadio.value : 'bullet';
                        console.log('선택된 대출 유형:', loanType);
                        
                        var loanTypeName = '';
                        
                        // 상환방식에 따른 이름 설정
                        if (loanType === 'bullet') {
                            loanTypeName = '만기일시상환';
                        } else if (loanType === 'equal-payment') {
                            loanTypeName = '원리금균등상환';
                        } else if (loanType === 'equal-principal') {
                            loanTypeName = '원금균등상환';
                        }
                        
                        // 입력값 가져오기
                        var loanAmountStr = document.getElementById('loan-amount').value.replace(/,/g, '');
                        var loanAmount = parseFloat(loanAmountStr) * 10000; // 만원 단위 입력
                        var interestRate = parseFloat(document.getElementById('interest-rate').value) / 100;
                        var loanTerm = parseInt(document.getElementById('loan-term').value);
                        var gracePeriod = 0;
                        
                        // 거치기간 설정 (원리금균등상환, 원금균등상환인 경우에만)
                        if (loanType === 'equal-payment' || loanType === 'equal-principal') {
                            gracePeriod = parseInt(document.getElementById('grace-period').value) || 0;
                            console.log('거치기간:', gracePeriod, '개월');
                        }
                        
                        console.log('입력값:', {
                            금액: loanAmount,
                            이자율: interestRate,
                            기간: loanTerm,
                            거치기간: gracePeriod
                        });
                        
                        // 입력값 검증
                        if (isNaN(loanAmount) || isNaN(interestRate) || isNaN(loanTerm) || loanAmount <= 0 || loanTerm <= 0 || interestRate < 0) {
                            alert('모든 필드를 유효한 값으로 입력해주세요.');
                            return;
                        }
                        
                        if ((loanType === 'equal-payment' || loanType === 'equal-principal') && gracePeriod >= loanTerm) {
                            alert('거치기간은 대출기간보다 작아야 합니다.');
                            return;
                        }
                        
                        // 요약 정보 표시
                        document.getElementById('res-loan-type').innerHTML = loanTypeName;
                        document.getElementById('res-loan-amount').innerHTML = formatNumber(loanAmountStr) + '만원';
                        document.getElementById('res-loan-rate').innerHTML = (interestRate * 100).toFixed(1) + '%';
                        
                        // 계산 결과 변수
                        var totalPrincipal = 0;
                        var totalInterest = 0;
                        var scheduleHTML = '';
                        var mobileScheduleHTML = '';
                        
                        // 상환 방식에 따른 계산
                        var monthlyRate = interestRate / 12;
                        
                        if (loanType === 'bullet') {
                            // 만기일시상환 계산
                            var monthlyInterest = loanAmount * monthlyRate;
                            
                            for (var i = 1; i <= loanTerm; i++) {
                                var principalPayment = (i === loanTerm) ? loanAmount : 0;
                                var interestPayment = monthlyInterest;
                                var payment = principalPayment + interestPayment;
                                var remainingBalance = (i === loanTerm) ? 0 : loanAmount;
                                
                                totalPrincipal += principalPayment;
                                totalInterest += interestPayment;
                                
                                // PC용 테이블 행 추가
                                var rowStyle = i % 2 === 0 ? 'background: #fff;' : 'background: #f9f6f0;';
                                scheduleHTML += '<tr style="' + rowStyle + '">';
                                scheduleHTML += '<td style="padding: 8px; text-align: center; border: 1px solid #d6c191;">' + i + '</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(principalPayment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(interestPayment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(payment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(remainingBalance)) + '원</td>';
                                scheduleHTML += '</tr>';
                                
                                // 모바일용 카드 추가 - 폰트 크기 조정됨
                                mobileScheduleHTML += '<div style="background: ' + (i % 2 === 0 ? '#fff' : '#f9f6f0') + '; border: 1px solid #d6c191; border-radius: 8px; margin-bottom: 8px; overflow: hidden;">';
                                mobileScheduleHTML += '<div style="background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; text-align: center; padding: 8px; font-weight: bold; font-size: 14px;">' + i + '회차</div>';
                                mobileScheduleHTML += '<div style="padding: 10px;">';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;"><span>납입원금:</span><span style="font-weight: bold;">' + formatNumber(Math.round(principalPayment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;"><span>이자:</span><span style="font-weight: bold;">' + formatNumber(Math.round(interestPayment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; border-top: 1px dashed #d6c191; padding-top: 5px; font-size: 13px;"><span>납입금액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(payment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; font-size: 13px;"><span>대출잔액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(remainingBalance)) + '원</span></div>';
                                mobileScheduleHTML += '</div>';
                                mobileScheduleHTML += '</div>';
                            }
                        } else if (loanType === 'equal-payment') {
                            // 원리금균등상환 계산
                            var repaymentTerm = loanTerm - gracePeriod;
                            var pmt = 0;
                            
                            if (monthlyRate === 0) {
                                // 이자율이 0인 경우
                                pmt = loanAmount / repaymentTerm;
                            } else {
                                // PMT 공식: PMT = P * r * (1+r)^n / ((1+r)^n - 1)
                                pmt = loanAmount * monthlyRate * Math.pow(1 + monthlyRate, repaymentTerm) / (Math.pow(1 + monthlyRate, repaymentTerm) - 1);
                            }
                            
                            var remainingBalance = loanAmount;
                            
                            for (var i = 1; i <= loanTerm; i++) {
                                var interestPayment = remainingBalance * monthlyRate;
                                var principalPayment = 0;
                                var payment = 0;
                                var isGracePeriod = i <= gracePeriod;
                                
                                if (isGracePeriod) {
                                    // 거치기간 동안 이자만 납부
                                    payment = interestPayment;
                                    principalPayment = 0;
                                } else {
                                    // 상환기간 동안 원리금균등상환
                                    payment = pmt;
                                    principalPayment = payment - interestPayment;
                                }
                                
                                remainingBalance -= principalPayment;
                                
                                // 마지막 달에 반올림 오차 보정
                                if (i === loanTerm) {
                                    principalPayment += remainingBalance;
                                    payment = principalPayment + interestPayment;
                                    remainingBalance = 0;
                                }
                                
                                totalPrincipal += principalPayment;
                                totalInterest += interestPayment;
                                
                                // PC용 테이블 행 추가
                                var rowStyle = i % 2 === 0 ? 'background: #fff;' : 'background: #f9f6f0;';
                                if (isGracePeriod) rowStyle += '; background-color: #f0eadb;';
                                
                                scheduleHTML += '<tr style="' + rowStyle + '">';
                                scheduleHTML += '<td style="padding: 8px; text-align: center; border: 1px solid #d6c191;">' + i + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: #8b6914; color: white;">거치</span>' : '') + '</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(principalPayment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(interestPayment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(payment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(remainingBalance)) + '원</td>';
                                scheduleHTML += '</tr>';
                                
                                // 모바일용 카드 추가 - 폰트 크기 조정됨
                                var cardBg = i % 2 === 0 ? '#fff' : '#f9f6f0';
                                if (isGracePeriod) cardBg = '#f0eadb';
                                
                                mobileScheduleHTML += '<div style="background: ' + cardBg + '; border: 1px solid #d6c191; border-radius: 8px; margin-bottom: 8px; overflow: hidden;">';
                                mobileScheduleHTML += '<div style="background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; text-align: center; padding: 8px; font-weight: bold; font-size: 14px;">' + i + '회차' + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: rgba(0,0,0,0.2); color: white;">거치기간</span>' : '') + '</div>';
                                mobileScheduleHTML += '<div style="padding: 10px;">';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;"><span>납입원금:</span><span style="font-weight: bold;">' + formatNumber(Math.round(principalPayment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;"><span>이자:</span><span style="font-weight: bold;">' + formatNumber(Math.round(interestPayment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; border-top: 1px dashed #d6c191; padding-top: 5px; font-size: 13px;"><span>납입금액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(payment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; font-size: 13px;"><span>대출잔액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(remainingBalance)) + '원</span></div>';
                                mobileScheduleHTML += '</div>';
                                mobileScheduleHTML += '</div>';
                            }
                        } else if (loanType === 'equal-principal') {
                            // 원금균등상환 계산
                            var repaymentTerm = loanTerm - gracePeriod;
                            var monthlyPrincipal = loanAmount / repaymentTerm;
                            var remainingBalance = loanAmount;
                            
                            for (var i = 1; i <= loanTerm; i++) {
                                var interestPayment = remainingBalance * monthlyRate;
                                var principalPayment = 0;
                                var isGracePeriod = i <= gracePeriod;
                                
                                if (isGracePeriod) {
                                    // 거치기간 동안 이자만 납부
                                    principalPayment = 0;
                                } else {
                                    // 상환기간 동안 원금균등상환
                                    principalPayment = monthlyPrincipal;
                                }
                                
                                var payment = principalPayment + interestPayment;
                                remainingBalance -= principalPayment;
                                
                                // 마지막 달에 반올림 오차 보정
                                if (i === loanTerm) {
                                    principalPayment += remainingBalance;
                                    payment = principalPayment + interestPayment;
                                    remainingBalance = 0;
                                }
                                
                                totalPrincipal += principalPayment;
                                totalInterest += interestPayment;
                                
                                // PC용 테이블 행 추가
                                var rowStyle = i % 2 === 0 ? 'background: #fff;' : 'background: #f9f6f0;';
                                if (isGracePeriod) rowStyle += '; background-color: #f0eadb;';
                                
                                scheduleHTML += '<tr style="' + rowStyle + '">';
                                scheduleHTML += '<td style="padding: 8px; text-align: center; border: 1px solid #d6c191;">' + i + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: #8b6914; color: white;">거치</span>' : '') + '</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(principalPayment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(interestPayment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(payment)) + '원</td>';
                                scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(remainingBalance)) + '원</td>';
                                scheduleHTML += '</tr>';
                                
                                // 모바일용 카드 추가 - 폰트 크기 조정됨
                                var cardBg = i % 2 === 0 ? '#fff' : '#f9f6f0';
                                if (isGracePeriod) cardBg = '#f0eadb';
                                
                                mobileScheduleHTML += '<div style="background: ' + cardBg + '; border: 1px solid #d6c191; border-radius: 8px; margin-bottom: 8px; overflow: hidden;">';
                                mobileScheduleHTML += '<div style="background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; text-align: center; padding: 8px; font-weight: bold; font-size: 14px;">' + i + '회차' + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: rgba(0,0,0,0.2); color: white;">거치기간</span>' : '') + '</div>';
                                mobileScheduleHTML += '<div style="padding: 10px;">';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;"><span>납입원금:</span><span style="font-weight: bold;">' + formatNumber(Math.round(principalPayment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;"><span>이자:</span><span style="font-weight: bold;">' + formatNumber(Math.round(interestPayment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; margin-bottom: 5px; border-top: 1px dashed #d6c191; padding-top: 5px; font-size: 13px;"><span>납입금액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(payment)) + '원</span></div>';
                                mobileScheduleHTML += '<div class="payment-item" style="display: flex; justify-content: space-between; font-size: 13px;"><span>대출잔액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(remainingBalance)) + '원</span></div>';
                                mobileScheduleHTML += '</div>';
                                mobileScheduleHTML += '</div>';
                            }
                        }
                        
                        console.log('계산 완료');
                        
                        // PC용 결과 표시
                        document.getElementById('payment-schedule').innerHTML = scheduleHTML;
                        document.getElementById('res-total-principal').innerHTML = formatNumber(Math.round(totalPrincipal)) + '원';
                        document.getElementById('res-total-interest').innerHTML = formatNumber(Math.round(totalInterest)) + '원';
                        document.getElementById('res-total-payment').innerHTML = formatNumber(Math.round(totalPrincipal + totalInterest)) + '원';
                        
                        // 모바일용 결과 표시
                        document.getElementById('mobile-payment-schedule').innerHTML = mobileScheduleHTML;
                        document.getElementById('mobile-total-principal').innerHTML = formatNumber(Math.round(totalPrincipal)) + '원';
                        document.getElementById('mobile-total-interest').innerHTML = formatNumber(Math.round(totalInterest)) + '원';
                        document.getElementById('mobile-total-payment').innerHTML = formatNumber(Math.round(totalPrincipal + totalInterest)) + '원';
                        
                        // 결과 보이기
                        document.getElementById('result-content').style.display = 'block';
                        
                        // 화면 크기에 따라 테이블/카드 표시 업데이트
                        updateDisplayMode();
                        
                        // 결과로 스크롤
                        window.scrollTo({
                            top: document.getElementById('loan-result').offsetTop - 50,
                            behavior: 'smooth'
                        });
                        
                    } catch (e) {
                        console.error('계산 중 오류 발생:', e);
                        alert('계산 중 오류가 발생했습니다: ' + e.message);
                    }
                }
                
                // 다시 계산하기 버튼 클릭 처리
                function resetCalculator() {
                    document.getElementById('result-content').style.display = 'none';
                    window.scrollTo({
                        top: document.querySelector('.loan-calculator').offsetTop - 50,
                        behavior: 'smooth'
                    });
                }
                
                // 페이지 로드 시 초기화
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('대출이자 계산기 초기화 완료');
                    toggleGracePeriod(); // 초기 상환방식에 따른 거치기간 표시 설정
                });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the plugin
$korean_loan_calculator = new KoreanLoanCalculator();