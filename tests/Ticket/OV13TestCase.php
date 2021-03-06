<?php
/*
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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

/**
 * Doctrine_Ticket_OV13_TestCase
 * 
 * change the way DQL is generated for array params in WHERE IN clause - keep single "?" and process it only after query cache is saved/restored
 *
 * @package     Doctrine
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision$
 */
class Doctrine_Ticket_OV13_TestCase extends Doctrine_UnitTestCase
{
    public function testTest()
    {
		$q = Doctrine_Query::create()
			->from('User')
			->whereIn('name', array('test', 'test2'))
			->andWhere('email_id IN ?', array(array(2, 3)))
			->andWhereIn('loginname', array())  // will be replaced with IN (NULL)
			->orWhereIn('loginname', array()); // will be ignored since there are other where clauses already

		$this->assertEqual($q->getDql(), ' FROM User WHERE name IN ? AND email_id IN ? AND loginname IN ?');
		$this->assertEqual($q->getSqlQuery(), 'SELECT e.id AS e__id, e.name AS e__name, e.loginname AS e__loginname, e.password AS e__password, e.type AS e__type, e.created AS e__created, e.updated AS e__updated, e.email_id AS e__email_id FROM entity e WHERE (e.name IN (?, ?) AND e.email_id IN (?, ?) AND e.loginname IN (NULL) AND (e.type = 0))');
		$this->assertEqual($q->getInternalParams(), array('test', 'test2', 2, 3));
		$this->assertEqual($q->getCountSqlQuery(), 'SELECT COUNT(*) AS num_results FROM entity e WHERE e.name IN (?, ?) AND e.email_id IN (?, ?) AND e.loginname IN (NULL) AND (e.type = 0)');
    }
}

